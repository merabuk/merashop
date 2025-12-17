<?php

declare(strict_types=1);

namespace App\Users\Domain\Event;

use App\Users\Domain\ValueObject\Id;

readonly class UserRegisteredEvent
{
    public function __construct(
        private Id $userId,
    ) {
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }
}
