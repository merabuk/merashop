<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Tests\Catalog\Support\ProductPriceMother;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ProductPriceTest extends TestCase
{
    #[DataProvider('invalidStateProvider')]
    public function testThrowsExceptionWhenHasInvalidState(
        TypeEnum $type,
        ?DateTimeImmutable $validFrom,
        ?DateTimeImmutable $validTo,
    ): void {
        $this->expectException(ProductPriceStateException::class);

        new ProductPrice(
            price: new Price(amount: 1000, currency: CurrencyEnum::UAH),
            type: Type::fromEnum($type),
            tax: new Tax(value: 1, type: TaxTypeEnum::Percentage),
            taxIncluded: TaxIncludedFlag::fromBool(true),
            validityPeriod: $validFrom && $validTo
                ? ValidityPeriod::fromDateTimeRange($validFrom, $validTo)
                : null,
        );
    }

    public static function invalidStateProvider(): iterable
    {
        yield 'filled time limit fields for non time limit price type' => [
            'type' => TypeEnum::Regular,
            'validFrom' => new DateTimeImmutable('+1 days'),
            'validTo' => new DateTimeImmutable('+31 days'),
        ];
        yield 'not filled time limit fields for time limit price type' => [
            'type' => TypeEnum::Sale,
            'validFrom' => null,
            'validTo' => null,
        ];
    }

    #[DataProvider('amountWithTaxProvider')]
    public function testItGetsAmountWithTax(
        int $amount,
        CurrencyEnum $currency,
        TypeEnum $type,
        float $tax,
        TaxTypeEnum $taxType,
        bool $taxIncluded,
        int $expectedAmount,
    ): void {
        $productPrice = ProductPriceMother::createWithData(
            amount: $amount,
            currency: $currency,
            type: $type,
            taxValue: $tax,
            taxType: $taxType,
            taxIncluded: $taxIncluded,
        );

        self::assertSame($expectedAmount, $productPrice->getAmountWithTax());
    }

    public static function amountWithTaxProvider(): iterable
    {
        yield 'tax included' => [
            'amount' => 1000,
            'currency' => CurrencyEnum::UAH,
            'type' => TypeEnum::Regular,
            'tax' => 1,
            'taxType' => TaxTypeEnum::Percentage,
            'taxIncluded' => true,
            'expectedAmount' => 1000,
        ];
        yield 'tax excluded' => [
            'amount' => 1000,
            'currency' => CurrencyEnum::UAH,
            'type' => TypeEnum::Regular,
            'tax' => 1,
            'taxType' => TaxTypeEnum::Percentage,
            'taxIncluded' => false,
            'expectedAmount' => 1010,
        ];
    }

    #[DataProvider('activeProvider')]
    public function testItIsActive(
        TypeEnum $type,
        ?DateTimeImmutable $validFrom,
        ?DateTimeImmutable $validTo,
        ?DateTimeImmutable $now,
        bool $expected,
    ): void {
        $productPrice = ProductPriceMother::createWithData(
            type: $type,
            validFrom: $validFrom,
            validTo: $validTo,
        );

        self::assertSame($expected, $productPrice->isActive($now));
    }

    public static function activeProvider(): iterable
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        yield 'not time limited price' => [
            'type' => TypeEnum::Regular,
            'validFrom' => null,
            'validTo' => null,
            'now' => $clock->now(),
            'expected' => true,
        ];
        yield 'not started time limited price' => [
            'type' => TypeEnum::Sale,
            'validFrom' => $clock->now()->modify('+1 day'),
            'validTo' => $clock->now()->modify('+31 days'),
            'now' => $clock->now(),
            'expected' => false,
        ];
        yield 'just started time limited price' => [
            'type' => TypeEnum::Sale,
            'validFrom' => $clock->now()->modify('+1 day'),
            'validTo' => $clock->now()->modify('+31 days'),
            'now' => $clock->now()->modify('+1 day'),
            'expected' => true,
        ];
        yield 'started time limited price' => [
            'type' => TypeEnum::Sale,
            'validFrom' => $clock->now()->modify('-1 day'),
            'validTo' => $clock->now()->modify('+30 days'),
            'now' => $clock->now(),
            'expected' => true,
        ];
        yield 'before ended time limited price' => [
            'type' => TypeEnum::Sale,
            'validFrom' => $clock->now()->modify('-31 day'),
            'validTo' => $clock->now(),
            'now' => $clock->now(),
            'expected' => true,
        ];
        yield 'ended time limited price' => [
            'type' => TypeEnum::Sale,
            'validFrom' => $clock->now()->modify('-31 day'),
            'validTo' => $clock->now()->modify('-1 day'),
            'now' => $clock->now(),
            'expected' => false,
        ];
    }
}
