<?php

namespace App\IdentityAccess\Domain\Factory\Contracts;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use DateTimeImmutable;

interface RefreshTokenFactoryInterface
{
    public function createForTest(
        string $tokenHash,
        string $accountUlid,
        IdentityTypeEnum $accountType,
        DateTimeImmutable $expiresAt,
    ): RefreshToken;
}
