<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeMagnitudeDimensionValueException;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Tests\Shared\BaseUnitTest;

final class DimensionValueTest extends BaseUnitTest
{
    public function testItCreatesValidDimensionValue(): void
    {
        $magnitude = 1234.5;
        $unit = AttributeOptionId::fromInt(123);

        $vo = new DimensionValue(
            magnitude: $magnitude,
            unit: $unit,
        );

        self::assertSame($magnitude, $vo->magnitude());
        self::assertSame($unit, $vo->getUnitOptionId());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $magnitude1 = 1234.5;
        $magnitude2 = 1234.6;
        $unit = AttributeOptionId::fromInt(123);

        $vo1 = new DimensionValue(magnitude: $magnitude1, unit: $unit);
        $vo2 = new DimensionValue(magnitude: $magnitude1, unit: $unit);
        $vo3 = new DimensionValue(magnitude: $magnitude2, unit: $unit);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testThrowsExceptionOnInvalidInput(): void
    {
        $invalidMagnitude = -1234.5;
        $unit = AttributeOptionId::fromInt(123);

        $this->expectException(InvalidProductAttributeMagnitudeDimensionValueException::class);

        new DimensionValue(magnitude: $invalidMagnitude, unit: $unit);
    }
}
