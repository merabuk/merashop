<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\BooleanFlagValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class TaxIncludedFlagTest extends TestCase
{
    use BooleanFlagValueObjectTrait;

    public function testItCreatesMainImageFlag(): void
    {
        $this->assertCreatesValidFlag(
            className: TaxIncludedFlag::class,
            value: true,
            expectedStringValue: TaxIncludedFlag::TRUE_STRING
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertBooleanFlagEquality(className: TaxIncludedFlag::class);
    }

    public function testItCreatesDefaultMainImageFlag(): void
    {
        $vo = TaxIncludedFlag::default();

        self::assertSame(TaxIncludedFlag::DEFAULT_VALUE, $vo->value());
    }
}
