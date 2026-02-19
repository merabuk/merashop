<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\AttributeFixture;
use App\Tests\Catalog\Support\AttributeMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait AttributeFactoryTrait
{
    protected function getAttributeMother(): AttributeMother
    {
        /** @var AttributeMother $mother */
        $mother = self::getContainer()->get(AttributeMother::class);

        return $mother;
    }

    protected function getAttributeFixture(): AttributeFixture
    {
        /** @var AttributeFixture $fixture */
        $fixture = self::getContainer()->get(AttributeFixture::class);

        return $fixture;
    }
}
