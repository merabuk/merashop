<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerValueTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validIntegerValueProvider')]
    public function testItCreatesValidIntegerValue(int $value): void
    {
        $vo = IntegerValue::fromInt($value);

        self::assertSame($value, $vo->value());
        self::assertSame((string) $value, (string) $vo);
    }

    public static function validIntegerValueProvider(): iterable
    {
        yield 'positive' => [10];
        yield 'zero' => [0];
        yield 'negative' => [-10];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertIntegerVOProvidesEqualityCheck(
            className: IntegerValue::class,
            value: 10,
            anotherValue: 20
        );
    }
}
