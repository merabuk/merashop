<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\StringAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Exception\ProductAttributeValue\UnsupportedAttributeTypeException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Service\ProductAttributeValue\AttributeValueResolver;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid as ProductUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class ProductApplicationFactory implements ProductApplicationFactoryInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $attributeReadRepository,
        private AttributeValueResolver $valueResolver,
    ) {
    }

    /**
     * @param int[] $categoryIds
     *
     * @return CategoryId[]
     *
     * @throws InvalidCategoryIdException
     */
    public function mapCategoryIds(array $categoryIds): array
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
     * @throws ProductPriceStateException
     * @throws UnsupportedAttributeTypeException
     */
    public function createFromCommand(CreateProductCommand $command, string $ulid): Product
    {
        return Product::create(
            ulid: ProductUlid::fromString($ulid),
            sku: Sku::fromString($command->sku),
            status: Status::fromString($command->status),
            translations: $this->mapTranslations($command->translations),
            prices: $this->mapPrices($command->prices),
            createdBy: AdminUlid::fromString($command->adminUlid),
            categoryIds: CategoryIdCollection::fromArray($this->mapCategoryIds($command->categoryIds)),
            attributeValues: $this->mapAttributeValues($command->attributeValues),
        );
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws ProductPriceStateException
     * @throws UnsupportedAttributeTypeException
     */
    public function updateFromCommand(Product $product, UpdateProductCommand $command): void
    {
        $product->update(
            sku: Sku::fromString($command->sku),
            status: Status::fromString($command->status),
            translations: $this->mapTranslations($command->translations),
            updatedBy: AdminUlid::fromString($command->adminUlid),
            prices: $this->mapPrices($command->prices),
            categoryIds: CategoryIdCollection::fromArray($this->mapCategoryIds($command->categoryIds)),
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
     * @throws InvalidCatalogValueObjectException
     * @throws ProductPriceStateException
     */
    private function mapPrices(array $prices): PriceCollection
    {
        return PriceCollection::fromArray(array_map(fn (ProductPriceData $p) => new ProductPrice(
            price: Price::fromPrimitives($p->amount, $p->currency),
            type: Type::fromString($p->type),
            tax: Tax::fromPrimitives($p->taxValue, $p->taxType),
            taxIncluded: TaxIncludedFlag::fromBool($p->taxIncluded),
            validityPeriod: $p->validFrom && $p->validTo
                ? ValidityPeriod::fromStrings(from: $p->validFrom, to: $p->validTo)
                : null,
        ), $prices));
    }

    /**
     * @param ProductAttributeValueData[] $attributeValuesData
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidAttributeIdException
     * @throws ProductAttributeValueStateException
     * @throws UnsupportedAttributeTypeException
     */
    private function mapAttributeValues(array $attributeValuesData): AttributeValueCollection
    {
        $attributes = $this->attributeReadRepository->findByIds($this->mapAttributeIds($attributeValuesData));

        $attributeValues = [];

        foreach ($attributeValuesData as $data) {
            $attribute = array_find($attributes, fn(Attribute $a) => $a->getId()->value() === $data->attributeId);

            if (!$attribute) {
                throw AttributeNotFoundException::withId($data->attributeId);
            }

            $valueData = $data->value;
            // TODO[attribute value]: add check type given valueData

            $result = match (true) {
                $valueData instanceof SelectAttributeValueData => [
                    // TODO[attribute value]: add check if option exists for attribute
                    ProductAttributeValue::createWithOption(
                        attributeId: AttributeId::fromInt($data->attributeId),
                        attributeOptionId: AttributeOptionId::fromInt($valueData->optionId)
                    )
                ],
                $valueData instanceof MultiSelectAttributeValueData => array_map(
                    // TODO[attribute value]: add check if options exists for attribute
                    fn (int $id) => ProductAttributeValue::createWithOption(
                        attributeId: AttributeId::fromInt($data->attributeId),
                        attributeOptionId: AttributeOptionId::fromInt($id)
                    ),
                    $valueData->optionIds
                ),
                $valueData instanceof StringAttributeValueData => [
                    ProductAttributeValue::createWithValue(
                        attributeId: AttributeId::fromInt($data->attributeId),
                        value: $this->valueResolver->resolve(AttributeTypeEnum::String, $valueData->translations)
                    )
                ],
                // TODO[attribute value]: add other types
                default => throw new UnsupportedAttributeTypeException()
            };

            foreach ($result as $pav) {
                $attributeValues[] = $pav;
            }
        }

        return AttributeValueCollection::fromArray($attributeValues);
    }
}
