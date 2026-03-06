<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\Product\InvalidProductVersionException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Domain\ValueObject\Product\Version;

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
        private Version $version,
        private readonly AdminUlid $createdBy,
        private ?AdminUlid $updatedBy = null,
        private array $categoryIds = [],
        private array $attributeValues = [],
    ) {
    }

    /**
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     *
     * @throws InvalidProductVersionException
     */
    public static function create(
        Ulid $ulid,
        Sku $sku,
        Price $price,
        Status $status,
        Translations $translations,
        AdminUlid $createdBy,
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
            version: Version::initial(),
            createdBy: $createdBy,
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

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getCreatedBy(): AdminUlid
    {
        return $this->createdBy;
    }

    public function getUpdatedBy(): ?AdminUlid
    {
        return $this->updatedBy;
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
        AdminUlid $updatedBy,
        array $categoryIds,
        array $attributeValues = [],
    ): void {
        $this->sku = $sku;
        $this->price = $price;
        $this->status = $status;
        $this->translations = $translations;
        $this->updatedBy = $updatedBy;
        $this->categoryIds = $categoryIds;
        $this->attributeValues = $attributeValues;
    }

    public function addAttributeValue(ProductAttributeValue $attributeValue): void
    {
        $this->attributeValues[] = $attributeValue;
    }
}
