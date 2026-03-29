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
        return self::getContainer()->get(ProductMother::class);
    }

    protected function getProductFixture(): ProductFixture
    {
        return self::getContainer()->get(ProductFixture::class);
    }
}
