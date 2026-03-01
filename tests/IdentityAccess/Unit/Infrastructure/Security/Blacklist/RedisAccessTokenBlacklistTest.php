<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Blacklist;

use App\IdentityAccess\Infrastructure\Security\Blacklist\RedisAccessTokenBlacklist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\MockClock;

final class RedisAccessTokenBlacklistTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $cachePool;
    private MockClock $clock;
    private RedisAccessTokenBlacklist $blacklist;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(CacheItemPoolInterface::class);
        $this->clock = new MockClock('2024-01-01 12:00:00');

        $this->blacklist = new RedisAccessTokenBlacklist(
            clock: $this->clock,
            identityAccessTokenBlacklistPool: $this->cachePool
        );
    }

    public function testRevokeWithPositiveTtl(): void
    {
        $jti = 'test-jwt-id';
        $now = $this->clock->now()->getTimestamp();
        $expiresAt = $now + 3600;
        $expectedTtl = 3600;

        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cachePool->expects(self::once())
            ->method('getItem')
            ->with($jti)
            ->willReturn($cacheItem);

        $cacheItem->expects(self::once())
            ->method('set')
            ->with(true)
            ->willReturn($cacheItem);

        $cacheItem->expects(self::once())
            ->method('expiresAfter')
            ->with($expectedTtl)
            ->willReturn($cacheItem);

        $this->cachePool->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        $this->blacklist->revoke($jti, $expiresAt);
    }

    public function testRevokeDoesNothingIfTokenAlreadyExpired(): void
    {
        $jti = 'expired-jwt-id';
        $now = $this->clock->now()->getTimestamp();
        $expiresAt = $now - 10;

        $this->cachePool->expects(self::never())->method('getItem');
        $this->cachePool->expects(self::never())->method('save');

        $this->blacklist->revoke($jti, $expiresAt);
    }

    public function testIsRevokedReturnsTrueWhenItemExistsInCache(): void
    {
        $jti = 'revoked-jti';

        $this->cachePool->expects(self::once())
            ->method('hasItem')
            ->with($jti)
            ->willReturn(true);

        self::assertTrue($this->blacklist->isRevoked($jti));
    }

    public function testIsRevokedReturnsFalseWhenItemDoesNotExist(): void
    {
        $jti = 'active-jti';

        $this->cachePool->expects(self::once())
            ->method('hasItem')
            ->with($jti)
            ->willReturn(false);

        self::assertFalse($this->blacklist->isRevoked($jti));
    }
}
