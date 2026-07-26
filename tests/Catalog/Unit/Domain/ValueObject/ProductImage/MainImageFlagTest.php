<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductImage;

use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\BooleanFlagValueObjectTrait;

final class MainImageFlagTest extends BaseUnitTest
{
    use BooleanFlagValueObjectTrait;

    public function testItCreatesMainImageFlag(): void
    {
        $this->assertCreatesValidFlag(
            className: MainImageFlag::class,
            value: true,
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertBooleanFlagEquality(className: MainImageFlag::class);
    }

    public function testItCreatesDefaultMainImageFlag(): void
    {
        $vo = MainImageFlag::default();

        self::assertSame(MainImageFlag::DEFAULT_VALUE, $vo->value());
    }
}
