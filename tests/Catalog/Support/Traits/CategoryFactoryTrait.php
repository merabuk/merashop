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
        /** @var CategoryMother $mother */
        $mother = self::getContainer()->get(CategoryMother::class);

        return $mother;
    }

    protected function getCategoryFixture(): CategoryFixture
    {
        /** @var CategoryFixture $fixture */
        $fixture = self::getContainer()->get(CategoryFixture::class);

        return $fixture;
    }
}
