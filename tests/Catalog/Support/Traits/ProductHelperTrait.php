<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Translation;
use App\Catalog\Domain\ValueObject\Product\Translations;
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

    protected function fillAndGetUpdateCommand(Product $product): UpdateProductCommand
    {
        return new UpdateProductCommand(
            id: $product->getId()->value(),
            sku: $product->getSku()->value(),
            status: $product->getStatus()->asString(),
            prices: self::getValidPrices($product->getPrices()),
            categoryIds: array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds()->all()),
            attributeValues: self::getValidAttributeValues($product->getAttributeValues()),
            translations: self::getValidTranslations($product->getTranslations()),
            images: array_map(fn (ProductImage $pi) => $pi->getUlid()->value(), $product->getImages()->all()),
            version: $product->getVersion()->value(),
            adminUlid: $product->getUpdatedBy()->value() ?? throw new RuntimeException("UpdatedBy mustn't be null"),
        );
    }

    /**
     * @return ProductPriceData[]
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
        return array_map(fn (ProductAttributeValue $pav) => new ProductAttributeValueData(
            attributeId: $pav->getAttributeId()->value(),
            value: $pav->getValue()->value(),
        ), $attributeValues->all());
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
}
