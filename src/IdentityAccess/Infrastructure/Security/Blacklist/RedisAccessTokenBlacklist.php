<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Blacklist;

use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Clock\ClockInterface;

final readonly class RedisAccessTokenBlacklist implements AccessTokenBlacklistInterface
{
    public function __construct(
        private ClockInterface $clock,
        private CacheItemPoolInterface $identityAccessTokenBlacklistPool,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function revoke(string $jti, int $expiresAt): void
    {
        $now = $this->clock->now()->getTimestamp();
        $ttl = $expiresAt - $now;

        if ($ttl > 0) {
            $item = $this->identityAccessTokenBlacklistPool->getItem($jti);
            $item->set(true);
            $item->expiresAfter($ttl);

            $this->identityAccessTokenBlacklistPool->save($item);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function isRevoked(string $jti): bool
    {
        return $this->identityAccessTokenBlacklistPool->hasItem($jti);
    }
}
