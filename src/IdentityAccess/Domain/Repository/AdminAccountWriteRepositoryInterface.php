<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\AdminAccount;

interface AdminAccountWriteRepositoryInterface
{
    public function save(AdminAccount $adminAccount): AdminAccount;

    public function delete(AdminAccount $adminAccount): void;
}
