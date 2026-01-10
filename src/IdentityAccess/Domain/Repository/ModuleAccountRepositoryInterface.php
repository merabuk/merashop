<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Repository;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\Ulid;

interface ModuleAccountRepositoryInterface
{
    public function findByClientId(ClientId $clientId): ?ModuleAccount;

    public function findByUlid(Ulid $ulid): ?ModuleAccount;
}
