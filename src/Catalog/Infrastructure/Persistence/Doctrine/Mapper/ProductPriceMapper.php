<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceIdException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\ProductPrice\Id;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductPrice;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;

final readonly class ProductPriceMapper
{
    /**
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductPriceIdException
     * @throws InvalidProductPriceTaxValueException
     * @throws EntityIdMissingException
     * @throws ProductPriceStateException
     */
    public function toDomain(OrmProductPrice $orm): ProductPrice
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        return new ProductPrice(
            price: new Price($orm->amount, $orm->currency),
            type: Type::fromEnum($orm->type),
            tax: new Tax((float) $orm->taxValue, $orm->taxType),
            taxIncluded: TaxIncludedFlag::fromBool($orm->taxIncluded),
            validFrom: $orm->validFrom ? ValidFrom::fromDateTime($orm->validFrom) : null,
            validTo: $orm->validTo ? ValidTo::fromDateTime($orm->validTo) : null,
            id: Id::fromInt($id)
        );
    }

    public function mapToExistingOrm(ProductPrice $domain, OrmProductPrice $orm): void
    {
        $orm->amount = $domain->getPrice()->getAmount();
        $orm->currency = $domain->getPrice()->getCurrency();
        $orm->type = $domain->getType()->value();
        $orm->taxValue = (string) $domain->getTax()->getValue();
        $orm->taxType = $domain->getTax()->getType();
        $orm->taxIncluded = $domain->getTaxIncluded()->value();
        $orm->validFrom = $domain->getValidFrom()?->value();
        $orm->validTo = $domain->getValidTo()?->value();
    }
}
