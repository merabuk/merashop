<?php

declare(strict_types=1);

namespace App\Users\Domain\Event;

use App\Users\Domain\Entity\User;

readonly class UserRegisteredEvent
{
    public function __construct(
        private User $user,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
