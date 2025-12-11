<?php

declare(strict_types=1);

namespace App\Users\Domain\Repository;

use App\Users\Domain\Entity\User;

interface UserWriteRepositoryInterface
{
    public function save(User $user): User;

    public function delete(User $user): void;
}
