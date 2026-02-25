<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Support\Traits;

use App\Tests\IdentityAccess\Support\AdminAccountFixture;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait AdminAccountFactoryTrait
{
    protected function getAdminAccountMother(): AdminAccountMother
    {
        /** @var AdminAccountMother $mother */
        $mother = self::getContainer()->get(AdminAccountMother::class);

        return $mother;
    }

    protected function getAdminAccountFixture(): AdminAccountFixture
    {
        /** @var AdminAccountFixture $fixture */
        $fixture = self::getContainer()->get(AdminAccountFixture::class);

        return $fixture;
    }
}
