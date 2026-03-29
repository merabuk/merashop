<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\AttributeFixture;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait AttributeFactoryTrait
{
    protected function getAttributeMother(): AttributeMother
    {
        return self::getContainer()->get(AttributeMother::class);
    }

    protected function getAttributeFixture(): AttributeFixture
    {
        return self::getContainer()->get(AttributeFixture::class);
    }

    protected function getAttributeOptionMother(): AttributeOptionMother
    {
        return self::getContainer()->get(AttributeOptionMother::class);
    }
}
