<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Application\Service\Product\AttributeValue\ProductAttributeValueProviderInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Exception\ProductAttributeValue\UnsupportedAttributeTypeException;
use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class ProductApplicationFactory implements ProductApplicationFactoryInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $attributeReadRepository,
        #[AutowireLocator(
            services: 'catalog.product_attribute_value_provider',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
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
     * @throws AttributeNotFoundException
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
     * @throws AttributeNotFoundException
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
     * @throws AttributeNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidAttributeIdException
     * @throws UnsupportedAttributeTypeException
     */
    private function mapAttributeValues(array $attributeValuesData): AttributeValueCollection
    {
        $attributes = $this->attributeReadRepository->findByIds(ids: $this->mapAttributeIds($attributeValuesData));

        $attributeValues = [];

        foreach ($attributeValuesData as $data) {
            try {
                $attribute = array_find($attributes, fn (Attribute $a) => $a->getId()->value() === $data->attributeId);

                if (!$attribute) {
                    // TODO[attribute]: add index support for exception
                    throw AttributeNotFoundException::withId($data->attributeId);
                }

                $type = $attribute->getType()->value();

                if (!$this->providers->has($type->value)) {
                    throw new UnsupportedAttributeTypeException(sprintf('Container does not have a value provider for attribute type: %s', $type->value));
                }

                $provider = $this->providers->get($type->value);

                if (false === $provider instanceof ProductAttributeValueProviderInterface) {
                    throw new UnsupportedAttributeTypeException(sprintf('Value provider "%s" must implement %s', $type->value, ProductAttributeValueProviderInterface::class));
                }

                foreach ($provider->handle($attribute, $data->value) as $pav) {
                    $attributeValues[] = $pav;
                }
            } catch (ContainerExceptionInterface $e) {
                throw new UnsupportedAttributeTypeException(message: 'Fail get value provider', previous: $e);
            }
        }

        return AttributeValueCollection::fromArray($attributeValues);
    }
}
