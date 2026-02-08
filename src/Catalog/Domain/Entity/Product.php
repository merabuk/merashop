<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Ulid;

class Product
{
    /**
     * @param array<string, array{name: string, description?: string}> $translations
     * @param CategoryId[] $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public function __construct(
        private readonly ?Id $id,
        private readonly Ulid $ulid,
        private Sku $sku,
        private Price $price,
        private StatusEnum $status,
        private array $translations = [],
        private array $categoryIds = [],
        private array $attributeValues = [],
    ) {
    }

    /**
     * @param Ulid $ulid
     * @param Sku $sku
     * @param Price $price
     * @param StatusEnum $status
     * @param array<string, array{name: string, description?: string}> $translations
     * @param CategoryId[] $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public static function create(
        Ulid $ulid,
        Sku $sku,
        Price $price,
        StatusEnum $status,
        array $translations = [],
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

    public function getStatus(): StatusEnum
    {
        return $this->status;
    }

    /**
     * @return array<string, array{name: string, description?: string}>
     */
    public function getTranslations(): array
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
     * @param Sku $sku
     * @param Price $price
     * @param StatusEnum $status
     * @param array<string, array{name: string, description?: string}> $translations
     * @param CategoryId[] $categoryIds
     */
    public function update(
        Sku $sku,
        Price $price,
        StatusEnum $status,
        array $translations,
        array $categoryIds
    ): void {
        $this->sku = $sku;
        $this->price = $price;
        $this->status = $status;
        $this->translations = $translations;
        $this->categoryIds = $categoryIds;
    }

    public function addAttributeValue(ProductAttributeValue $attributeValue): void
    {
        $this->attributeValues[] = $attributeValue;
    }
}
