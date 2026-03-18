<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\ProductFixture;
use App\Tests\Catalog\Support\ProductMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ProductFactoryTrait
{
    protected function getProductMother(): ProductMother
    {
        /** @var ProductMother $mother */
        $mother = self::getContainer()->get(ProductMother::class);

        return $mother;
    }

    protected function getProductFixture(): ProductFixture
    {
        /** @var ProductFixture $fixture */
        $fixture = self::getContainer()->get(ProductFixture::class);

        return $fixture;
    }
}
