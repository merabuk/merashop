<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeColorValueException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ColorValueTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validColorValueProvider')]
    public function testItCreatesValidColorValue(
        string $value,
        string $expectedValue,
    ): void {
        $vo = ColorValue::fromString($value);

        self::assertEquals($expectedValue, $vo->value());
        self::assertSame($expectedValue, (string) $vo);
    }

    public static function validColorValueProvider(): iterable
    {
        yield 'upper' => ['#AABBCC', '#AABBCC'];
        yield 'lower' => ['#ddeeff', '#DDEEFF'];
        yield 'trimmed' => ['  #aabbcc  ', '#AABBCC'];
        yield 'short' => ['#abc', '#AABBCC'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: ColorValue::class,
            value: '#AABBCC',
            anotherValue: '#ddeeff',
        );
    }

    #[DataProvider('invalidColorValueProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidProductAttributeColorValueException::class);
        ColorValue::fromString($invalidValue);
    }

    public static function invalidColorValueProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too short' => ['#ab'];
        yield 'too long' => ['#aabbccdd'];
        yield 'invalid hex' => ['aabbcc'];
    }
}
