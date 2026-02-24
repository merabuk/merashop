<?php

namespace App\IdentityAccess\Domain\Service;

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
