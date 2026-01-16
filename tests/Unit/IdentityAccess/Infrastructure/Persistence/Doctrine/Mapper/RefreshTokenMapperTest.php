<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper;

use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Mapper\RefreshTokenMapper;
use PHPUnit\Framework\TestCase;

final class RefreshTokenMapperTest extends TestCase
{
    private RefreshTokenMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RefreshTokenMapper();
    }

    public function testToDoctrineOrm(): void
    {
        $ulid = Ulid::fromString('01ARZ3NDEKTSV4RRFFQ6KHNQZY');
        $expiresAt = new \DateTimeImmutable('+30 days');
        $domain = new RefreshToken(
            tokenHash: TokenHash::fromString('test-token'),
            accountUlid: $ulid,
            expiresAt: new ExpiresAt($expiresAt),
            id: 123
        );

        $orm = $this->mapper->toDoctrineOrm($domain);

        self::assertInstanceOf(OrmRefreshToken::class, $orm);
        self::assertSame(123, $orm->id);
        self::assertSame('test-token', $orm->token);
        self::assertSame($ulid->value(), $orm->accountUlid);
        self::assertSame($expiresAt, $orm->expiresAt);
    }

    public function testFromDoctrineOrm(): void
    {
        $ulid = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';
        $expiresAt = new \DateTimeImmutable('+30 days');
        $orm = new OrmRefreshToken();
        $orm->setId(456);
        $orm->token = 'orm-token';
        $orm->accountUlid = $ulid;
        $orm->expiresAt = $expiresAt;

        $domain = $this->mapper->fromDoctrineOrm($orm);

        self::assertInstanceOf(RefreshToken::class, $domain);
        self::assertSame(456, $domain->getId());
        self::assertSame('orm-token', $domain->getTokenHash()->value());
        self::assertSame($ulid, $domain->getAccountUlid()->value());
        self::assertSame($expiresAt, $domain->getExpiresAt()->value());
    }
}
