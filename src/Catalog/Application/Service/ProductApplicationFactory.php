<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\Product\ProductPricesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Catalog\Domain\Exception\ProductAttribute\UnsupportedAttributeTypeException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid as ProductUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class ProductApplicationFactory implements ProductApplicationFactoryInterface
{
    /**
     * @param int[] $categoryIds
     *
     * @return CategoryId[]
     *
     * @throws InvalidCategoryIdException
     */
    public function mapCategoriesIds(array $categoryIds): array
    {
        return array_map(fn (int $id) => CategoryId::fromInt($id), $categoryIds);
    }

    /**
     * @param ProductAttributeValueData[] $attributeValues
     *
     * @return AttributeId[]
     *
     * @throws InvalidAttributeIdException
     */
    public function mapAttributeIds(array $attributeValues): array
    {
        return array_map(fn (ProductAttributeValueData $v) => AttributeId::fromInt($v->attributeId), $attributeValues);
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws ProductPricesEmptyException
     * @throws ProductPriceUniqueException
     * @throws UnsupportedAttributeTypeException
     */
    public function createFromCommand(CreateProductCommand $command, string $newUlid): Product
    {
        return Product::create(
            ulid: ProductUlid::fromString($newUlid),
            sku: Sku::fromString($command->sku),
            status: Status::fromString($command->status),
            translations: $this->mapTranslations($command->translations),
            prices: $this->mapPrices($command->prices),
            createdBy: AdminUlid::fromString($command->adminUlid),
            categoryIds: $this->mapCategoriesIds($command->categoryIds),
            attributeValues: $this->mapAttributeValues($command->attributeValues),
        );
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function updateFromCommand(Product $product, UpdateProductCommand $command): void
    {
        $product->update(
            sku: Sku::fromString($command->sku),
            status: Status::fromString($command->status),
            translations: $this->mapTranslations($command->translations),
            updatedBy: AdminUlid::fromString($command->adminUlid),
            prices: $this->mapPrices($command->prices),
            categoryIds: $this->mapCategoriesIds($command->categoryIds),
            attributeValues: $this->mapAttributeValues($command->attributeValues),
        );
    }

    /**
     * @param ProductTranslationData[] $translations
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapTranslations(array $translations): Translations
    {
        return Translations::fromArray(array_map(fn (ProductTranslationData $t) => [
            'name' => $t->name,
            'description' => $t->description,
        ], $translations));
    }

    /**
     * @param ProductPriceData[] $prices
     *
     * @return ProductPrice[]
     *
     * @throws InvalidCatalogValueObjectException
     */
    private function mapPrices(array $prices): array
    {
        return array_map(fn (ProductPriceData $p) => new ProductPrice(
            price: Price::fromPrimitives($p->amount, $p->currency),
            type: Type::fromString($p->type),
            tax: Tax::fromPrimitives($p->taxValue, $p->taxType),
            taxIncluded: TaxIncludedFlag::fromBool($p->taxIncluded),
            validFrom: $p->validFrom ? ValidFrom::fromString($p->validFrom) : null,
            validTo: $p->validTo ? ValidTo::fromString($p->validTo) : null,
        ), $prices);
    }

    /**
     * @param ProductAttributeValueData[] $values
     *
     * @return ProductAttributeValue[]
     *
     * @throws InvalidCatalogValueObjectException
     * @throws UnsupportedAttributeTypeException
     */
    private function mapAttributeValues(array $values): array
    {
        return array_map(fn (ProductAttributeValueData $v) => ProductAttributeValue::createWithRawValue(
            attributeId: AttributeId::fromInt($v->attributeId),
            value: $v->value
        ), $values);
    }
}
