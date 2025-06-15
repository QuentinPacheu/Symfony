<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class TimestampableListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt(new \DateTimeImmutable());
        }
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }
    }
}