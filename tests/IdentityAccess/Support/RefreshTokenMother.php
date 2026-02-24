<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;
use App\IdentityAccess\Domain\Service\RefreshTokenFactoryInterface;
use App\IdentityAccess\Domain\Service\TokenHasherInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountUlid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use DateTimeImmutable;

final readonly class RefreshTokenMother
{
    private const string DEFAULT_TOKEN_HASH = '3c469e9d6c5875d37a43f353d4f88e61fcf812c66eee3457465a40b0da4153e0'; // token

    public function __construct(
        private RefreshTokenFactoryInterface $refreshTokenFactory,
        private TokenHasherInterface $tokenHasher,
        private UlidGeneratorInterface $ulidGenerator,
    ) {
    }

    /**
     * @throws InvalidIdentityAccessValueObjectException
     */
    public static function createWithData(
        ?string $tokenHash = null,
        ?string $accountUlid = null,
        IdentityTypeEnum $accountType = IdentityTypeEnum::User,
        ?DateTimeImmutable $expiresAt = null,
        ?int $id = null,
    ): RefreshToken {
        return new RefreshToken(
            tokenHash: TokenHash::fromString($tokenHash ?? self::DEFAULT_TOKEN_HASH),
            accountUlid: AccountUlid::fromString($accountUlid ?? self::defineUlidByType($accountType)),
            accountType: AccountType::fromEnum($accountType),
            expiresAt: ExpiresAt::fromDateTime($expiresAt ?? new DateTimeImmutable('+1 hour')),
            id: $id ? Id::fromInt($id) : null
        );
    }

    public function create(
        ?string $token = null,
        ?string $accountUlid = null,
        IdentityTypeEnum $accountType = IdentityTypeEnum::User,
        ?DateTimeImmutable $expiresAt = null,
    ): RefreshToken {
        return $this->refreshTokenFactory->createForTest(
            tokenHash: $token ? $this->tokenHasher->hash($token) : self::DEFAULT_TOKEN_HASH,
            accountUlid: $accountUlid ?? $this->ulidGenerator->next(),
            accountType: $accountType,
            expiresAt: $expiresAt ?? new DateTimeImmutable('+1 hour'),
        );
    }

    /**
     * @return RefreshToken[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }

    /**
     * The method is designed for convenience within the module.
     */
    private static function defineUlidByType(IdentityTypeEnum $accountType): string
    {
        return match ($accountType) {
            IdentityTypeEnum::User => UserAccountMother::DEFAULT_ULID,
            IdentityTypeEnum::Module => ModuleAccountMother::DEFAULT_ULID,
            IdentityTypeEnum::Admin => AdminAccountMother::DEFAULT_ULID,
        };
    }
}
