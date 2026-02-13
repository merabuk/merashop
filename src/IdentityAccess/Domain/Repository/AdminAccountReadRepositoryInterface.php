<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;

interface AdminAccountReadRepositoryInterface
{
    public function findById(Id $id): ?AdminAccount;

    public function findByEmail(EmailAddress $email): ?AdminAccount;

    public function findByUlid(Ulid $ulid): ?AdminAccount;

    public function existsByEmail(EmailAddress $email): bool;
}
