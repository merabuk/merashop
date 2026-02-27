<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\ModuleAccount;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;

final readonly class ModuleAccountFixture
{
    public function __construct(
        private ModuleAccountMother $moduleAccountMother,
        private ModuleAccountWriteRepositoryInterface $repository,
    ) {
    }

    /**
     * @param ?string[] $scopes
     */
    public function create(
        ?string $ulid = null,
        ?string $clientId = null,
        ?string $clientSecretHash = null,
        ?array $scopes = null,
    ): ModuleAccount {
        $moduleAccount = $this->moduleAccountMother->create(
            ulid: $ulid,
            clientId: $clientId,
            clientSecretHash: $clientSecretHash,
            scopes: $scopes
        );

        return $this->repository->save($moduleAccount);
    }

    /**
     * @return ModuleAccount[]
     */
    public function createMany(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->create();
        }

        return $items;
    }
}
