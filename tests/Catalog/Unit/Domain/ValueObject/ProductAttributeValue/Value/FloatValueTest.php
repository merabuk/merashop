<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\FloatValue;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class FloatValueTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validFloatValueProvider')]
    public function testItCreatesValidFloatValue(float $value): void
    {
        $vo = FloatValue::fromFloat($value);

        self::assertSame($value, $vo->value());
        self::assertSame((string) $value, (string) $vo);
    }

    public static function validFloatValueProvider(): iterable
    {
        yield 'positive float' => [10.5];
        yield 'float with zero' => [0.0];
        yield 'negative float' => [-10.5];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertFloatVOProvidesEqualityCheck(
            className: FloatValue::class,
            value: 10.5,
            anotherValue: 10.6
        );
    }
}
