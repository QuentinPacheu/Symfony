<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\AddPointsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AddPointsMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(AddPointsMessage $message): void
    {
        // Trouver tous les utilisateurs actifs
        $activeUsers = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.actif = :actif')
            ->setParameter('actif', true)
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($activeUsers as $user) {
            $user->setPoints($user->getPoints() + 1000);
            $count++;
        }

        $this->entityManager->flush();

        $this->logger->info(sprintf(
            '1000 points ajoutés à %d utilisateurs actifs',
            $count
        ));
    }
}