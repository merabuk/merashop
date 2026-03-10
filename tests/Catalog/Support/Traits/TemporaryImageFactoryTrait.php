<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Tests\Catalog\Support\TemporaryImageFixture;
use App\Tests\Catalog\Support\TemporaryImageMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait TemporaryImageFactoryTrait
{
    protected function getTemporaryImageMother(): TemporaryImageMother
    {
        /** @var TemporaryImageMother $mother */
        $mother = self::getContainer()->get(TemporaryImageMother::class);

        return $mother;
    }

    protected function getTemporaryImageFixture(): TemporaryImageFixture
    {
        /** @var TemporaryImageFixture $fixture */
        $fixture = self::getContainer()->get(TemporaryImageFixture::class);

        return $fixture;
    }
}
