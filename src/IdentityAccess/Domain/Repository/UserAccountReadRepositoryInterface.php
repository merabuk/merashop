<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;

interface UserAccountReadRepositoryInterface
{
    public function findById(int $id): ?UserAccount;

    public function findByEmail(EmailAddress $email): ?UserAccount;

    public function findByUlid(Ulid $ulid): ?UserAccount;
}
