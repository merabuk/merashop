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
        return self::getContainer()->get(TemporaryImageMother::class);
    }

    protected function getTemporaryImageFixture(): TemporaryImageFixture
    {
        return self::getContainer()->get(TemporaryImageFixture::class);
    }
}
