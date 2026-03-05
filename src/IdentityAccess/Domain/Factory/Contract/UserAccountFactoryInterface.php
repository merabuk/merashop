<?php

namespace App\IdentityAccess\Domain\Factory\Contract;

use App\IdentityAccess\Domain\Entity\UserAccount;

interface UserAccountFactoryInterface
{
    /**
     * @param string[] $roles
     */
    public function createForTest(
        string $ulid,
        string $email,
        string $passwordHash,
        array $roles,
    ): UserAccount;
}
