<?php

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Entity\User;
use App\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class NotificationMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(NotificationMessage $message): void
    {
        // Trouver tous les admins
        $admins = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        $label = sprintf(
            '%s: %s (ID: %d) - %s',
            $message->getType(),
            $message->getEntityName(),
            $message->getEntityId(),
            (new \DateTime())->format('d/m/Y H:i:s')
        );

        // Créer une notification pour chaque admin
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setLabel($label);
            $notification->setUser($admin);
            
            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}