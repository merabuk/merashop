<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait IdentityAccessCacheTrait
{
    protected function clearTokenBlacklistCache(): void
    {
        /** @var CacheItemPoolInterface $pool */
        $pool = self::getContainer()->get('identity_access.token_blacklist');
        $pool->clear();
    }
}
