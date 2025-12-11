<?php

declare(strict_types=1);

namespace App\Users\Domain\Repository;

use App\Users\Domain\Entity\User;
use App\Users\Domain\ValueObject\EmailAddress;

interface UserReadRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(EmailAddress $email): ?User;
}
