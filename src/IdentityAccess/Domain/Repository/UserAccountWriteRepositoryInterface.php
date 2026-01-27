<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\UserAccount;

interface UserAccountWriteRepositoryInterface
{
    public function save(UserAccount $userAccount): UserAccount;

    public function delete(UserAccount $userAccount): void;
}
