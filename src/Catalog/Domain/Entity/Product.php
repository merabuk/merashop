<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;

class Product
{
    /**
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public function __construct(
        private readonly ?Id $id,
        private readonly Ulid $ulid,
        private Sku $sku,
        private Price $price,
        private Status $status,
        private Translations $translations,
        private array $categoryIds = [],
        private array $attributeValues = [],
    ) {
    }

    /**
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public static function create(
        Ulid $ulid,
        Sku $sku,
        Price $price,
        Status $status,
        Translations $translations,
        array $categoryIds = [],
        array $attributeValues = [],
    ): self {
        return new self(
            id: null,
            ulid: $ulid,
            sku: $sku,
            price: $price,
            status: $status,
            translations: $translations,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
        );
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getSku(): Sku
    {
        return $this->sku;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getTranslations(): Translations
    {
        return $this->translations;
    }

    /**
     * @return CategoryId[]
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    /**
     * @return ProductAttributeValue[]
     */
    public function getAttributeValues(): array
    {
        return $this->attributeValues;
    }

    /**
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public function update(
        Sku $sku,
        Price $price,
        Status $status,
        Translations $translations,
        array $categoryIds,
        array $attributeValues = [],
    ): void {
        $this->sku = $sku;
        $this->price = $price;
        $this->status = $status;
        $this->translations = $translations;
        $this->categoryIds = $categoryIds;
        $this->attributeValues = $attributeValues;
    }

    public function addAttributeValue(ProductAttributeValue $attributeValue): void
    {
        $this->attributeValues[] = $attributeValue;
    }
}
