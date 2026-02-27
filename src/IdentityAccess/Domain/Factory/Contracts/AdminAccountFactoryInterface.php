<?php

namespace App\IdentityAccess\Domain\Factory\Contracts;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;

interface AdminAccountFactoryInterface
{
    /**
     * @param string[] $roles
     */
    public function createForTest(
        string $ulid,
        string $email,
        string $passwordHash,
        array $roles,
        StatusEnum $status,
    ): AdminAccount;
}
