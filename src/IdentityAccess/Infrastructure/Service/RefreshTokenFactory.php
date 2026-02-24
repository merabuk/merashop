<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Service;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountUlidException;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\Service\RefreshTokenFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountUlid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use DateTimeImmutable;

final readonly class RefreshTokenFactory implements RefreshTokenFactoryInterface
{
    /**
     * @throws InvalidRefreshTokenAccountUlidException
     * @throws InvalidRefreshTokenTokenHashException
     */
    public function createForTest(
        string $tokenHash,
        string $accountUlid,
        IdentityTypeEnum $accountType,
        DateTimeImmutable $expiresAt,
    ): RefreshToken {
        return RefreshToken::create(
            token: TokenHash::fromString($tokenHash),
            accountUlid: AccountUlid::fromString($accountUlid),
            accountType: AccountType::fromEnum($accountType),
            expiresAt: ExpiresAt::fromDateTime($expiresAt),
        );
    }
}
