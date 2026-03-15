<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Catalog\Domain\Factory\Contract\ProductPriceFactoryInterface;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use DateTimeImmutable;

final readonly class ProductPriceFactory implements ProductPriceFactoryInterface
{
    /**
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductPriceTaxValueException
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
    ): ProductPrice {
        return new ProductPrice(
            price: new Price(amount: $amount, currency: $currency),
            type: Type::fromEnum($type),
            tax: new Tax(value: $taxValue, type: $taxType),
            taxIncluded: TaxIncludedFlag::fromBool($taxIncluded),
            validFrom: $validFrom ? ValidFrom::fromDateTime($validFrom) : null,
            validTo: $validTo ? ValidTo::fromDateTime($validTo) : null,
        );
    }
}
