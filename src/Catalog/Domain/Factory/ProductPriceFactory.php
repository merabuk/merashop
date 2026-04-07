<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidityPeriodException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceVersionException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\Factory\Contract\ProductPriceFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use DateTimeImmutable;

final readonly class ProductPriceFactory implements ProductPriceFactoryInterface
{
    /**
     * @throws InvalidAdminUlidException
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductPriceTaxValueException
     * @throws InvalidProductPriceValidityPeriodException
     * @throws InvalidProductPriceVersionException
     * @throws ProductPriceStateException
     */
    public function createForTest(
        int $amount,
        CurrencyEnum $currency,
        TypeEnum $type,
        float $taxValue,
        TaxTypeEnum $taxType,
        bool $taxIncluded,
        ?DateTimeImmutable $validFrom,
        ?DateTimeImmutable $validTo,
        string $createdByUlid,
    ): ProductPrice {
        return ProductPrice::create(
            price: new Price(amount: $amount, currency: $currency),
            type: Type::fromEnum($type),
            tax: new Tax(value: $taxValue, type: $taxType),
            taxIncluded: TaxIncludedFlag::fromBool($taxIncluded),
            createdBy: AdminUlid::fromString($createdByUlid),
            validityPeriod: $validFrom && $validTo
                ? ValidityPeriod::fromDateTimeRange($validFrom, $validTo)
                : null,
        );
    }
}
