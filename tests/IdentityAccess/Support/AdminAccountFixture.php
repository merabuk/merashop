<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;

final readonly class AdminAccountFixture
{
    public function __construct(
        private AdminAccountMother $adminAccountMother,
        private AdminAccountWriteRepositoryInterface $repository,
    ) {
    }

    /**
     * @param ?string[] $roles
     */
    public function create(
        ?string $ulid = null,
        ?string $email = null,
        ?string $passwordHash = null,
        ?array $roles = null,
        StatusEnum $status = StatusEnum::Active,
    ): AdminAccount {
        $adminAccount = $this->adminAccountMother->create(
            ulid: $ulid,
            email: $email,
            passwordHash: $passwordHash,
            roles: $roles,
            status: $status
        );

        return $this->repository->save($adminAccount);
    }

    /**
     * @return AdminAccount[]
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
