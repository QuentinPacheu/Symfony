<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final readonly class NotificationMessage
{
    public function __construct(
        private string $type,
        private string $entityName,
        private int $entityId,
        private ?int $userId = null
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}