<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\ModuleAccount;

interface ModuleAccountWriteRepositoryInterface
{
    public function save(ModuleAccount $moduleAccount): ModuleAccount;

    public function delete(ModuleAccount $moduleAccount): void;
}
