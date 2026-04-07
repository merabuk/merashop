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
        return self::getContainer()->get(AdminAccountMother::class);
    }

    protected function getAdminAccountFixture(): AdminAccountFixture
    {
        return self::getContainer()->get(AdminAccountFixture::class);
    }
}
