<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
use App\Catalog\Domain\Exception\Product\InvalidProductVersionException;
use App\Catalog\Domain\Exception\Product\ProductImagesMainImageException;
use App\Catalog\Domain\Exception\Product\ProductImageUniqueException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Domain\ValueObject\Product\Version;

class Product
{
    public function __construct(
        private readonly Ulid $ulid,
        private Sku $sku,
        private Status $status,
        private Translations $translations,
        private Version $version,
        private readonly AdminUlid $createdBy,
        private PriceCollection $prices,
        private CategoryIdCollection $categoryIds,
        private AttributeValueCollection $attributeValues,
        private ImageCollection $images,
        private ?AdminUlid $updatedBy = null,
        private readonly ?Id $id = null,
    ) {
    }

    /**
     * @throws InvalidProductImageItemException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     * @throws InvalidProductVersionException
     */
    public static function create(
        Ulid $ulid,
        Sku $sku,
        Status $status,
        Translations $translations,
        PriceCollection $prices,
        AdminUlid $createdBy,
        CategoryIdCollection $categoryIds,
        AttributeValueCollection $attributeValues,
    ): self {
        return new self(
            ulid: $ulid,
            sku: $sku,
            status: $status,
            translations: $translations,
            version: Version::initial(),
            createdBy: $createdBy,
            prices: $prices,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
            images: ImageCollection::empty(),
        );
    }

    public function update(
        Sku $sku,
        Status $status,
        Translations $translations,
        AdminUlid $updatedBy,
        PriceCollection $prices,
        CategoryIdCollection $categoryIds,
        AttributeValueCollection $attributeValues,
    ): void {
        $this->sku = $sku;
        $this->status = $status;
        $this->translations = $translations;
        $this->updatedBy = $updatedBy;
        $this->prices = $prices;
        $this->categoryIds = $categoryIds;
        $this->attributeValues = $attributeValues;
    }

    /**
     * @throws InvalidProductImageItemException
     */
    public function addImage(ProductImage $image): void
    {
        $this->images = $this->images->add($image);
    }

    public function setImages(ImageCollection $images): void
    {
        $this->images = $images;
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

    public function getPrices(): PriceCollection
    {
        return $this->prices;
    }

    public function getCategoryIds(): CategoryIdCollection
    {
        return $this->categoryIds;
    }

    public function getAttributeValues(): AttributeValueCollection
    {
        return $this->attributeValues;
    }

    public function getImages(): ImageCollection
    {
        return $this->images;
    }
}
