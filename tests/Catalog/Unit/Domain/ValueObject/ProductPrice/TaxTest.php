<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxTypeException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Shared\Domain\Enum\TaxTypeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaxTest extends TestCase
{
    #[DataProvider('validTaxData')]
    public function testItCreatesValidTax(
        float $value,
        TaxTypeEnum $type,
    ): void {
        $vo = new Tax(value: $value, type: $type);

        self::assertSame($value, $vo->getValue());
        self::assertSame($type, $vo->getType());
        self::assertSame(self::getExpectedStringValue($value, $type), (string) $vo);
    }

    #[DataProvider('validTaxData')]
    public function testItCreatesValidTaxFromPrimitives(
        float $value,
        TaxTypeEnum $type,
    ): void {
        $vo = Tax::fromPrimitives(value: $value, type: $type->value);

        self::assertSame($value, $vo->getValue());
        self::assertSame($type, $vo->getType());
        self::assertSame(self::getExpectedStringValue($value, $type), (string) $vo);
    }

    public static function validTaxData(): iterable
    {
        foreach (TaxTypeEnum::cases() as $type) {
            yield $type->name => [
                'value' => 3.14159265359,
                'type' => $type,
            ];
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $value = 3.14159265359;
        $type1 = TaxTypeEnum::Fixed;
        $type2 = TaxTypeEnum::Percentage;

        $vo1 = new Tax(value: $value, type: $type1);
        $vo2 = new Tax(value: $value, type: $type1);
        $vo3 = new Tax(value: $value, type: $type2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('factoryMethodProvider')]
    public function testItCreatesCorrectTaxFromFactoryMethods(
        Tax $vo,
        float $expectedValue,
        TaxTypeEnum $expectedType,
        string $expectedMethod,
    ): void {
        self::assertSame($expectedValue, $vo->getValue());
        self::assertSame($expectedType, $vo->getType());
        self::assertSame(self::getExpectedStringValue($expectedValue, $expectedType), (string) $vo);
        self::assertTrue($vo->{$expectedMethod}());
    }

    public static function factoryMethodProvider(): iterable
    {
        $value = 3.14159265359;
        foreach (TaxTypeEnum::cases() as $type) {
            yield $type->name => [
                'vo' => Tax::{$type->value}($value),
                'expectedValue' => $value,
                'expectedType' => $type,
                'expectedMethod' => "is{$type->name}",
            ];
        }
    }

    #[DataProvider('edgeCasesProvider')]
    public function testItCreatesEdgeCases(
        int $value,
        TaxTypeEnum $type,
        float $expectedValue,
    ): void {
        $vo = Tax::fromPrimitives(value: $value, type: $type->value);

        self::assertSame($expectedValue, $vo->getValue());
    }

    public static function edgeCasesProvider(): iterable
    {
        yield 'zero fixed rate' => [
            'value' => Tax::MIN_FIXED_TAX_VALUE,
            'type' => TaxTypeEnum::Fixed,
            'expectedValue' => Tax::MIN_FIXED_TAX_VALUE,
        ];
        yield 'zero percentage rate' => [
            'value' => Tax::MIN_PERCENTAGE_TAX_VALUE,
            'type' => TaxTypeEnum::Percentage,
            'expectedValue' => Tax::MIN_PERCENTAGE_TAX_VALUE,
        ];
        yield '100% percentage rate' => [
            'value' => Tax::MAX_PERCENTAGE_TAX_VALUE,
            'type' => TaxTypeEnum::Percentage,
            'expectedValue' => Tax::MAX_PERCENTAGE_TAX_VALUE,
        ];
    }

    #[DataProvider('invalidTaxData')]
    public function testThrowsExceptionOnInvalidInput(
        int $value,
        string $type,
        string $expectedExceptionClass,
    ): void {
        $this->expectException($expectedExceptionClass);
        Tax::fromPrimitives(value: $value, type: $type);
    }

    public static function invalidTaxData(): iterable
    {
        yield 'negative fixed rate' => [
            'value' => -10,
            'type' => TaxTypeEnum::Fixed->value,
            'expectedExceptionClass' => InvalidProductPriceTaxValueException::class,
        ];
        yield 'negative percentage rate' => [
            'value' => -10,
            'type' => TaxTypeEnum::Percentage->value,
            'expectedExceptionClass' => InvalidProductPriceTaxValueException::class,
        ];
        yield 'more than 100% percentage rate' => [
            'value' => 101,
            'type' => TaxTypeEnum::Percentage->value,
            'expectedExceptionClass' => InvalidProductPriceTaxValueException::class,
        ];
        yield 'invalid tax type' => [
            'value' => 10,
            'type' => 'invalid_type',
            'expectedExceptionClass' => InvalidProductPriceTaxTypeException::class,
        ];
    }

    #[DataProvider('calculateTaxAmountProvider')]
    public function testItCalculatesTaxAmount(
        Tax $vo,
        int $amount,
        int $expectedAmount,
    ): void {
        self::assertSame($expectedAmount, $vo->calculateFor($amount));
    }

    public static function calculateTaxAmountProvider(): iterable
    {
        yield 'fixed tax' => [
            'vo' => Tax::fixed(100),
            'amount' => 1000,
            'expectedAmount' => 100,
        ];
        yield 'percentage tax' => [
            'vo' => Tax::percentage(10),
            'amount' => 1000,
            'expectedAmount' => 100,
        ];
        yield 'percentage tax with rounding' => [
            'vo' => Tax::percentage(33),
            'amount' => 999,
            'expectedAmount' => 330,
        ];
    }

    private static function getExpectedStringValue(float $value, TaxTypeEnum $type): string
    {
        return sprintf('%s_%s', number_format($value, 2), $type->value);
    }
}
