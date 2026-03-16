<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\InvalidProductAttributeValueItemException;
use App\Catalog\Domain\Exception\Product\InvalidProductCategoryIdItemException;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceItemException;
use App\Catalog\Domain\Exception\Product\InvalidProductSkuException;
use App\Catalog\Domain\Exception\Product\InvalidProductUlidException;
use App\Catalog\Domain\Exception\Product\InvalidProductVersionException;
use App\Catalog\Domain\Exception\Product\ProductPricesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Catalog\Domain\Factory\Contract\ProductFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class ProductFactory implements ProductFactoryInterface
{
    /**
     * @param array<string, array{name: string, description?: string}> $translations
     * @param ProductPrice[]                                           $prices
     * @param CategoryId[]                                             $categoryIds
     * @param ProductAttributeValue[]                                  $attributeValues
     *
     * @throws InvalidAdminUlidException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws InvalidProductPriceItemException
     * @throws InvalidProductSkuException
     * @throws InvalidProductUlidException
     * @throws InvalidProductVersionException
     * @throws ProductPriceUniqueException
     * @throws ProductPricesEmptyException
     * @throws InvalidProductAttributeValueItemException
     * @throws InvalidProductCategoryIdItemException
     */
    public function createForTest(
        string $ulid,
        string $sku,
        StatusEnum $status,
        array $translations,
        array $prices,
        string $createdByUlid,
        array $categoryIds,
        array $attributeValues,
    ): Product {
        return Product::create(
            ulid: Ulid::fromString($ulid),
            sku: Sku::fromString($sku),
            status: Status::fromEnum($status),
            translations: Translations::fromArray($translations),
            prices: PriceCollection::fromArray($prices),
            createdBy: AdminUlid::fromString($createdByUlid),
            categoryIds: CategoryIdCollection::fromArray($categoryIds),
            attributeValues: AttributeValueCollection::fromArray($attributeValues),
        );
    }
}
