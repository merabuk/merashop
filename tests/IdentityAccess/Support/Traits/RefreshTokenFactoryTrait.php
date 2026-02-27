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
        /** @var RefreshTokenMother $mother */
        $mother = self::getContainer()->get(RefreshTokenMother::class);

        return $mother;
    }

    protected function getRefreshTokenFixture(): RefreshTokenFixture
    {
        /** @var RefreshTokenFixture $fixture */
        $fixture = self::getContainer()->get(RefreshTokenFixture::class);

        return $fixture;
    }
}
