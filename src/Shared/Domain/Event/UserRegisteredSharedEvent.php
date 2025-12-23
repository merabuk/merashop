<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

readonly class UserRegisteredSharedEvent implements DomainEventInterface
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
    ) {
    }

    public function getEventName(): DomainEventNameEnum
    {
        return DomainEventNameEnum::UserRegistered;
    }
}
