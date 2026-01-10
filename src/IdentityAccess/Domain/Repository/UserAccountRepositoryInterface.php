<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;

use App\IdentityAccess\Domain\ValueObject\Ulid;

interface UserAccountRepositoryInterface
{
    public function findByEmail(EmailAddress $email): ?UserAccount;

    public function findByUlid(Ulid $ulid): ?UserAccount;
}
