<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;

final readonly class UserAccountFixture
{
    public function __construct(
        private UserAccountMother $userAccountMother,
        private UserAccountWriteRepositoryInterface $repository,
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
    ): UserAccount {
        $userAccount = $this->userAccountMother->create(
            ulid: $ulid,
            email: $email,
            passwordHash: $passwordHash,
            roles: $roles
        );

        return $this->repository->save($userAccount);
    }

    /**
     * @return UserAccount[]
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
