<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use App\Tests\IdentityAccess\Support\RefreshTokenFixture;
use App\Tests\IdentityAccess\Support\RefreshTokenMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait RefreshTokenFactoryTrait
{
    protected function getRefreshTokenMother(): RefreshTokenMother
    {
        return self::getContainer()->get(RefreshTokenMother::class);
    }

    protected function getRefreshTokenFixture(): RefreshTokenFixture
    {
        return self::getContainer()->get(RefreshTokenFixture::class);
    }
}
