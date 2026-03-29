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
        return self::getContainer()->get(UserAccountMother::class);
    }

    protected function getUserAccountFixture(): UserAccountFixture
    {
        return self::getContainer()->get(UserAccountFixture::class);
    }
}
