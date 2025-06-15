<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Message\NotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PurchaseService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus
    ) {
    }

    public function purchaseProduct(User $user, Product $product): bool
    {
        // Vérifier si l'utilisateur est actif
        if (!$user->isActif()) {
            throw new \Exception('Votre compte est désactivé. Vous ne pouvez pas effectuer d\'achat.');
        }

        // Vérifier si l'utilisateur a assez de points
        if ($user->getPoints() < $product->getPrice()) {
            throw new \Exception('Vous n\'avez pas assez de points pour acheter ce produit.');
        }

        // Déduire les points
        $user->setPoints($user->getPoints() - $product->getPrice());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Envoyer une notification aux admins
        $this->bus->dispatch(new NotificationMessage(
            'ACHAT',
            sprintf('%s %s a acheté %s', $user->getFirstname(), $user->getName(), $product->getName()),
            $product->getId(),
            $user->getId()
        ));

        return true;
    }
}