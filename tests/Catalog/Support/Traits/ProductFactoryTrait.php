<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Catalog\Support\ProductFixture;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\ProductPriceMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ProductFactoryTrait
{
    protected function getProductMother(): ProductMother
    {
        return self::getContainer()->get(ProductMother::class);
    }

    protected function getProductFixture(): ProductFixture
    {
        return self::getContainer()->get(ProductFixture::class);
    }

    protected function getProductImageMother(): ProductImageMother
    {
        return self::getContainer()->get(ProductImageMother::class);
    }

    protected function getProductPriceMother(): ProductPriceMother
    {
        return self::getContainer()->get(ProductPriceMother::class);
    }

    protected function getProductAttributeValueMother(): ProductAttributeValueMother
    {
        return self::getContainer()->get(ProductAttributeValueMother::class);
    }
}
