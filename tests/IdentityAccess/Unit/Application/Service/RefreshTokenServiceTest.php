<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Service;

use App\IdentityAccess\Application\Service\RefreshTokenService;
use App\IdentityAccess\Domain\Entity\RefreshToken;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\IdentityAccess\Domain\Service\RandomTokenGeneratorInterface;
use App\IdentityAccess\Domain\Service\TokenHasherInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\RefreshTokenMother;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class RefreshTokenServiceTest extends TestCase
{
    public function testItCreatesRefreshTokenCorrectly(): void
    {
        $randomGenerator = $this->createMock(RandomTokenGeneratorInterface::class);
        $writeRepository = $this->createMock(RefreshTokenWriteRepositoryInterface::class);
        $tokenHasher = $this->createMock(TokenHasherInterface::class);

        $now = new DateTimeImmutable('2024-01-01 10:00:00');
        $clock = new MockClock($now);
        $ttl = 3600;

        $service = new RefreshTokenService(
            tokenGenerator: $randomGenerator,
            writeRepository: $writeRepository,
            tokenHasher: $tokenHasher,
            clock:$clock,
            ttl: $ttl
        );

        $plainToken = 'plain_text_token';
        $randomGenerator->method('generateRefreshToken')->willReturn($plainToken);
        $tokenHasher->method('hash')->with($plainToken)->willReturn('hashed_token');

        $expectedExpiry = $now->modify("+{$ttl} seconds");

        $writeRepository->expects(self::once())->method('deleteAllPrevious');
        $writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (RefreshToken $token) => $token->getExpiresAt()->equalsWithDateTime($expectedExpiry)));

        $result = $service->create(UserAccountMother::DEFAULT_ULID, IdentityTypeEnum::User);

        self::assertSame($plainToken, $result->token);
        self::assertSame($ttl, $result->expiresIn);
    }

    public function testItRevokesRefreshTokenCorrectly(): void
    {
        $writeRepository = $this->createMock(RefreshTokenWriteRepositoryInterface::class);

        $writeRepository->expects(self::once())->method('deleteAllPrevious');

        $service = new RefreshTokenService(
            tokenGenerator: $this->createMock(RandomTokenGeneratorInterface::class),
            writeRepository: $writeRepository,
            tokenHasher: $this->createMock(TokenHasherInterface::class),
            clock: new MockClock(),
            ttl: 3600
        );
        $service->revoke(RefreshTokenMother::createWithData());
    }
}
