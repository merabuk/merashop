<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceCurrencyException;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Service\Utility\CurrencyHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    #[DataProvider('validPriceProvider')]
    public function testItCreatesValidPrice(
        int $amount,
        CurrencyEnum $currency,
    ): void {
        $vo = new Price(amount: $amount, currency: $currency);

        self::assertSame($amount, $vo->getAmount());
        self::assertSame($currency, $vo->getCurrency());
        self::assertSame(self::getExpectedStringValue($amount, $currency), (string) $vo);
    }

    #[DataProvider('validPriceProvider')]
    public function testItCreatesValidPriceFromPrimitives(
        int $amount,
        CurrencyEnum $currency,
    ): void {
        $vo = Price::fromPrimitives(amount: $amount, currency: $currency->value);

        self::assertSame($amount, $vo->getAmount());
        self::assertSame($currency, $vo->getCurrency());
        self::assertSame(self::getExpectedStringValue($amount, $currency), (string) $vo);
    }

    public static function validPriceProvider(): iterable
    {
        foreach (CurrencyEnum::cases() as $currency) {
            yield $currency->name => [
                'amount' => 1000,
                'currency' => $currency,
            ];
        }
    }

    public function testItCreatesPriceWithMinAmount(): void
    {
        $minAmount = Price::MIN_AMOUNT;
        $vo = new Price(amount: $minAmount, currency: CurrencyEnum::USD);
        self::assertSame($minAmount, $vo->getAmount());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $amount = 1000;
        $currency1 = CurrencyEnum::USD;
        $currency2 = CurrencyEnum::UAH;

        $vo1 = new Price(amount: $amount, currency: $currency1);
        $vo2 = new Price(amount: $amount, currency: $currency1);
        $vo3 = new Price(amount: $amount, currency: $currency2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidPriceProvider')]
    public function testThrowsExceptionOnInvalidInput(
        int $amount,
        string $currency,
        string $expectedExceptionClass,
    ): void {
        $this->expectException($expectedExceptionClass);
        Price::fromPrimitives(amount: $amount, currency: $currency);
    }

    public static function invalidPriceProvider(): iterable
    {
        yield 'negative amount' => [
            'amount' => -1000,
            'currency' => CurrencyEnum::USD->value,
            'expectedExceptionClass' => InvalidProductPriceAmountException::class,
        ];
        yield 'not supported currency' => [
            'amount' => 1000,
            'currency' => 'EUR',
            'expectedExceptionClass' => InvalidProductPriceCurrencyException::class,
        ];
        yield 'not existing currency' => [
            'amount' => 1000,
            'currency' => 'NOT_EXISTING_CURRENCY',
            'expectedExceptionClass' => InvalidProductPriceCurrencyException::class,
        ];
    }

    private static function getExpectedStringValue(int $amount, CurrencyEnum $currency): string
    {
        return CurrencyHelper::formatPrice($amount, $currency);
    }
}
