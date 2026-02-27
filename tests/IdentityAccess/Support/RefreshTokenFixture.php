<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use DateTimeImmutable;

final readonly class RefreshTokenFixture
{
    public function __construct(
        private RefreshTokenMother $refreshTokenMother,
        private RefreshTokenWriteRepositoryInterface $repository,
    ) {
    }

    public function create(
        ?string $tokenHash = null,
        ?string $accountUlid = null,
        IdentityTypeEnum $accountType = IdentityTypeEnum::User,
        ?DateTimeImmutable $expiresAt = null,
    ): RefreshToken {
        $refreshToken = $this->refreshTokenMother->create(
            token: $tokenHash,
            accountUlid: $accountUlid,
            accountType: $accountType,
            expiresAt: $expiresAt
        );

        return $this->repository->save($refreshToken);
    }

    /**
     * @return RefreshToken[]
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
