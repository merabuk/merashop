<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

readonly class UserRegisteredSharedEvent
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
    ) {
    }
}
