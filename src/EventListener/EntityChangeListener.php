<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Notification;
use App\Message\NotificationMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Product::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Product::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Product::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdateUser', entity: User::class)]
class EntityChangeListener
{
    public function __construct(
        private MessageBusInterface $bus
    ) {
    }

    public function postPersist(Product $product, PostPersistEventArgs $event): void
    {
        $this->bus->dispatch(new NotificationMessage(
            'CREATION',
            'Product: ' . $product->getName(),
            $product->getId()
        ));
    }

    public function postUpdate(Product $product, PostUpdateEventArgs $event): void
    {
        $this->bus->dispatch(new NotificationMessage(
            'MODIFICATION',
            'Product: ' . $product->getName(),
            $product->getId()
        ));
    }

    public function postRemove(Product $product, PostRemoveEventArgs $event): void
    {
        $this->bus->dispatch(new NotificationMessage(
            'SUPPRESSION',
            'Product: ' . $product->getName(),
            $product->getId()
        ));
    }

    public function preUpdateUser(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('actif') && !$user->isActif()) {
            $notification = new Notification();
            $notification->setLabel('Votre compte a été désactivé - ' . (new \DateTime())->format('d/m/Y H:i:s'));
            $notification->setUser($user);
            
            $em = $event->getObjectManager();
            $em->persist($notification);
        }
    }
}