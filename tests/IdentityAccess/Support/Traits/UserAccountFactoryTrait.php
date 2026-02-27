<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use App\Tests\IdentityAccess\Support\UserAccountFixture;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait UserAccountFactoryTrait
{
    protected function getUserAccountMother(): UserAccountMother
    {
        /** @var UserAccountMother $mother */
        $mother = self::getContainer()->get(UserAccountMother::class);

        return $mother;
    }

    protected function getUserAccountFixture(): UserAccountFixture
    {
        /** @var UserAccountFixture $fixture */
        $fixture = self::getContainer()->get(UserAccountFixture::class);

        return $fixture;
    }
}
