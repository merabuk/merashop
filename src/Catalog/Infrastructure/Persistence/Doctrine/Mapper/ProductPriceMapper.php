<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Id;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Catalog\Domain\ValueObject\ProductPrice\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductPrice;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;

final readonly class ProductPriceMapper
{
    /**
     * @throws EntityFieldMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws ProductPriceStateException
     */
    public function toDomain(OrmProductPrice $orm): ProductPrice
    {
        $id = $orm->id ?? throw EntityFieldMissingException::forEntityId($orm::class);

        return new ProductPrice(
            price: new Price(
                amount: $orm->amount ?? throw EntityFieldMissingException::forField(field: 'amount', className: $orm::class),
                currency: $orm->currency
            ),
            type: Type::fromEnum($orm->type),
            tax: new Tax(
                value: (float) ($orm->taxValue ?? throw EntityFieldMissingException::forField(field: 'taxValue', className: $orm::class)),
                type: $orm->taxType ?? throw EntityFieldMissingException::forField(field: 'taxType', className: $orm::class)),
            taxIncluded: TaxIncludedFlag::fromBool($orm->taxIncluded),
            version: Version::fromInt($orm->version ?? throw EntityFieldMissingException::forField(field: 'version', className: $orm::class)),
            createdBy: AdminUlid::fromString($orm->createdBy ?? throw EntityFieldMissingException::forField(field: 'createdBy', className: $orm::class)),
            validityPeriod: $orm->validFrom && $orm->validTo
                ? ValidityPeriod::fromDateTimeRange($orm->validFrom, $orm->validTo)
                : null,
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            id: Id::fromInt($id)
        );
    }

    public function mapToExistingOrm(ProductPrice $domain, OrmProductPrice $orm): void
    {
        $orm->amount = $domain->getPrice()->getAmount();
        $orm->taxValue = (string) $domain->getTax()->getValue();
        $orm->taxType = $domain->getTax()->getType();
        $orm->taxIncluded = $domain->getTaxIncluded()->value();
        $orm->validFrom = $domain->getValidityPeriod()?->getFrom()->value();
        $orm->validTo = $domain->getValidityPeriod()?->getTo()?->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();
    }
}
