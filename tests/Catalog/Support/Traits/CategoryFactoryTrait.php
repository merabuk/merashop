<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\CategoryFixture;
use App\Tests\Catalog\Support\CategoryMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait CategoryFactoryTrait
{
    protected function getCategoryMother(): CategoryMother
    {
        return self::getContainer()->get(CategoryMother::class);
    }

    protected function getCategoryFixture(): CategoryFixture
    {
        return self::getContainer()->get(CategoryFixture::class);
    }
}
