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
        /** @var CustomerProfileMother $mother */
        $mother = self::getContainer()->get(CustomerProfileMother::class);

        return $mother;
    }

    protected function getCustomerProfileFixture(): CustomerProfileFixture
    {
        /** @var CustomerProfileFixture $fixture */
        $fixture = self::getContainer()->get(CustomerProfileFixture::class);

        return $fixture;
    }
}
