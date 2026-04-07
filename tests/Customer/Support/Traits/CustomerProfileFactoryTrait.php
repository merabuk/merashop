<?php

declare(strict_types=1);

namespace App\Tests\Customer\Support\Traits;

use App\Tests\Customer\Support\CustomerProfileFixture;
use App\Tests\Customer\Support\CustomerProfileMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait CustomerProfileFactoryTrait
{
    protected function getCustomerProfileMother(): CustomerProfileMother
    {
        return self::getContainer()->get(CustomerProfileMother::class);
    }

    protected function getCustomerProfileFixture(): CustomerProfileFixture
    {
        return self::getContainer()->get(CustomerProfileFixture::class);
    }
}
