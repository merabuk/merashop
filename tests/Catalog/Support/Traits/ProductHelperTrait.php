<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\BooleanAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\ColorAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\DateAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\FloatAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\StringAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\TextAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\UrlAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Translation;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DateValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\FloatValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedStringValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedTextValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\UrlValue;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use RuntimeException;

trait ProductHelperTrait
{
    protected function fillAndGetCreateCommand(Product $product): CreateProductCommand
    {
        return new CreateProductCommand(
            sku: $product->getSku()->value(),
            status: $product->getStatus()->asString(),
            prices: self::getValidPrices($product->getPrices()),
            categoryIds: array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds()->all()),
            attributeValues: self::getValidAttributeValues($product->getAttributeValues()),
            translations: self::getValidTranslations($product->getTranslations()),
            images: array_map(fn (ProductImage $pi) => $pi->getUlid()->value(), $product->getImages()->all()),
            adminUlid: $product->getCreatedBy()->value(),
        );
    }

    protected function fillAndGetUpdateCommand(
        Product $product,
        ?int $version = null,
        ?string $sku = null,
        ?array $images = null,
    ): UpdateProductCommand {
        return new UpdateProductCommand(
            id: $product->getId()->value(),
            sku: $sku ?? $product->getSku()->value(),
            status: $product->getStatus()->asString(),
            prices: self::getValidPrices($product->getPrices()),
            categoryIds: array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds()->all()),
            attributeValues: self::getValidAttributeValues($product->getAttributeValues()),
            translations: self::getValidTranslations($product->getTranslations()),
            images: $images ?? array_map(fn (ProductImage $pi) => $pi->getUlid()->value(), $product->getImages()->all()),
            version: $version ?? $product->getVersion()->value(),
            adminUlid: $product->getUpdatedBy()->value() ?? throw new RuntimeException("UpdatedBy mustn't be null"),
        );
    }

    /**
     * @return array<int, ProductPriceData>
     */
    protected static function getValidPrices(PriceCollection $prices): array
    {
        return array_map(fn (ProductPrice $price) => new ProductPriceData(
            amount: $price->getPrice()->getAmount(),
            currency: $price->getPrice()->getCurrencyCode(),
            type: $price->getType()->asString(),
            taxValue: $price->getTax()->getValue(),
            taxType: $price->getTax()->getTypeAsString(),
            taxIncluded: $price->getTaxIncluded()->value(),
            validFrom: $price->getValidityPeriod()?->getFrom()->value(),
            validTo: $price->getValidityPeriod()?->getTo()->value(),
        ), $prices->all());
    }

    /**
     * @return ProductAttributeValueData[]
     */
    protected static function getValidAttributeValues(AttributeValueCollection $attributeValues): array
    {
        /**
         * @var array<int, ProductAttributeValue[]> $grouped
         */
        $grouped = [];

        foreach ($attributeValues->all() as $attributeValue) {
            $grouped[$attributeValue->getAttributeId()->value()][] = $attributeValue;
        }

        $attributeValues = [];
        foreach ($grouped as $attributeId => $values) {
            $attributeValues[] = new ProductAttributeValueData(
                attributeId: $attributeId,
                value: self::mapProductAttributeValue($values),
            );
        }

        return $attributeValues;
    }

    /**
     * @return ProductTranslationData[]
     */
    protected static function getValidTranslations(Translations $translations): array
    {
        return array_map(fn (Translation $t) => new ProductTranslationData(
            name: $t->name,
            description: $t->description,
        ), $translations->all());
    }

    /**
     * @param int[] $categoryIds
     *
     * @return CategoryId[]
     */
    private static function mapToCategoryIds(array $categoryIds): array
    {
        return array_map(fn (int $categoryId) => CategoryId::fromInt($categoryId), $categoryIds);
    }

    /**
     * @param ProductAttributeValueData[] $attributeValues
     *
     * @return AttributeId[]
     */
    private static function mapToAttributeIds(array $attributeValues): array
    {
        return array_map(fn (ProductAttributeValueData $d) => AttributeId::fromInt($d->attributeId), $attributeValues);
    }

    /**
     * @param string[] $imagesUlids
     *
     * @return TemporaryImageUlid[]
     */
    private static function mapToTemporaryImagesUlids(array $imagesUlids, ImageCollection $imageCollection): array
    {
        $filteredUlids = array_filter($imagesUlids, fn (string $ulid) => null === $imageCollection->getByUlid($ulid));

        return array_values(array_map(fn (string $ulid) => TemporaryImageUlid::fromString($ulid), $filteredUlids));
    }

    /**
     * @param string[] $imagesUlids
     *
     * @return ProductImageUlid[]
     */
    private static function mapToProductImagesUlidsForDelete(
        array $imagesUlids,
        ImageCollection $imageCollection,
    ): array {
        $map = array_flip($imagesUlids);

        $productImageUlids = [];
        foreach ($imageCollection as $image) {
            if (!isset($map[$image->getUlid()->value()])) {
                $productImageUlids[] = $image->getUlid();
            }
        }

        return $productImageUlids;
    }

    /**
     * @param ProductAttributeValue[] $pav
     */
    private static function mapProductAttributeValue(array $pav): AttributeValueDataInterface
    {
        if (count($pav) > 1) {
            $optionIds = [];
            foreach ($pav as $attributeValue) {
                $optionIds[] = $attributeValue->getAttributeOptionId()->value();
            }

            return new MultiSelectAttributeValueData(optionIds: $optionIds);
        }

        $value = $pav[0]->getValue();

        if (null === $value) {
            return new SelectAttributeValueData(optionId: $pav[0]->getAttributeOptionId()->value());
        }

        return match (true) {
            $value instanceof BooleanValue => new BooleanAttributeValueData(value: $value->value()),
            $value instanceof ColorValue => new ColorAttributeValueData(value: $value->value()),
            $value instanceof DateValue => new DateAttributeValueData(value: $value->value()->format('Y-m-d')),
            $value instanceof DimensionValue => new DimensionAttributeValueData(
                magnitude: $value->magnitude(),
                unitOptionId: $pav[0]->getAttributeOptionId()->value(),
            ),
            $value instanceof FloatValue => new FloatAttributeValueData(value: $value->value()),
            $value instanceof IntegerValue => new IntegerAttributeValueData(value: $value->value()),
            $value instanceof LocalizedStringValue => new StringAttributeValueData(translations: $value->value()),
            $value instanceof LocalizedTextValue => new TextAttributeValueData(translations: $value->value()),
            $value instanceof UrlValue => new UrlAttributeValueData(value: $value->value()),
            default => throw new RuntimeException(sprintf('Invalid attribute value type: %s', get_debug_type($value))),
        };
    }
}
