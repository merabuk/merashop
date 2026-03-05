<?php

namespace App\IdentityAccess\Domain\Factory\Contract;

use App\IdentityAccess\Domain\Entity\ModuleAccount;

interface ModuleAccountFactoryInterface
{
    /**
     * @param string[] $scopes
     */
    public function createForTest(
        string $ulid,
        string $clientId,
        string $clientSecretHash,
        array $scopes,
    ): ModuleAccount;
}
