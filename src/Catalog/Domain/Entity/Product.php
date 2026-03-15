<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\Product\InvalidProductVersionException;
use App\Catalog\Domain\Exception\Product\ProductPricesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Domain\ValueObject\Product\Version;
use App\Shared\Domain\Enum\CurrencyEnum;
use DateTimeImmutable;

class Product
{
    /**
     * @param ProductPrice[]          $prices
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     * @param ProductImage[]          $images
     *
     * @throws ProductPricesEmptyException
     * @throws ProductPriceUniqueException
     */
    public function __construct(
        private readonly Ulid $ulid,
        private Sku $sku,
        private Status $status,
        private Translations $translations,
        private Version $version,
        private readonly AdminUlid $createdBy,
        private array $prices,
        private ?AdminUlid $updatedBy = null,
        private array $categoryIds = [],
        private array $attributeValues = [],
        private array $images = [],
        private readonly ?Id $id = null,
    ) {
        $this->ensurePricesAreNotEmpty();
        $this->ensurePricesAreUnique($this->prices);
    }

    /**
     * @param ProductPrice[]          $prices
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     *
     * @throws InvalidProductVersionException
     * @throws ProductPricesEmptyException
     * @throws ProductPriceUniqueException
     */
    public static function create(
        Ulid $ulid,
        Sku $sku,
        Status $status,
        Translations $translations,
        array $prices,
        AdminUlid $createdBy,
        array $categoryIds = [],
        array $attributeValues = [],
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
        );
    }

    /**
     * @param ProductPrice[]          $prices
     * @param CategoryId[]            $categoryIds
     * @param ProductAttributeValue[] $attributeValues
     */
    public function update(
        Sku $sku,
        Status $status,
        Translations $translations,
        AdminUlid $updatedBy,
        array $prices,
        array $categoryIds,
        array $attributeValues = [],
    ): void {
        $this->sku = $sku;
        $this->status = $status;
        $this->translations = $translations;
        $this->updatedBy = $updatedBy;
        $this->prices = $prices;
        $this->categoryIds = $categoryIds;
        $this->attributeValues = $attributeValues;
    }

    public function addImage(ProductImage $image): void
    {
        if ($this->checkImagesContains($image)) {
            return;
        }

        if ($image->isMain()->isTrue()) {
            $this->resetMainImage();
        }

        if (empty($this->images)) {
            $image->setAsMain();
        }

        $this->images[] = $image;
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

    /**
     * @return ProductPrice[]
     */
    public function getPrices(): array
    {
        return $this->prices;
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
     * @return ProductImage[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    public function getActivePrice(CurrencyEnum $currency, DateTimeImmutable $now): ?ProductPrice
    {
        $activePrices = array_filter(
            $this->prices,
            fn (ProductPrice $p) => $p->getPrice()->getCurrency() === $currency && $p->isActive($now)
        );

        return array_find($activePrices, fn (ProductPrice $p) => $p->getType()->isSale())
            ?? array_find($activePrices, fn (ProductPrice $p) => $p->getType()->isRegular());
    }

    /**
     * @throws ProductPricesEmptyException
     */
    private function ensurePricesAreNotEmpty(): void
    {
        if (empty($this->prices)) {
            throw ProductPricesEmptyException::becauseItIsEmpty();
        }
    }

    /**
     * @param ProductPrice[] $prices
     *
     * @throws ProductPriceUniqueException
     */
    private function ensurePricesAreUnique(array $prices): void
    {
        $keys = [];
        foreach ($prices as $price) {
            $priceType = $price->getType()->asString();
            $currency = $price->getPrice()->getCurrencyCode();
            $key = sprintf('%s_%s', $priceType, $currency);
            if (isset($keys[$key])) {
                throw ProductPriceUniqueException::duplicatePriceTypeForCurrency(priceType: $priceType, currency: $currency);
            }
            $keys[$key] = true;
        }
    }

    private function resetMainImage(): void
    {
        foreach ($this->images as $image) {
            $image->unsetMain();
        }
    }

    private function checkImagesContains(ProductImage $image): bool
    {
        return array_any(
            array: $this->images,
            callback: fn (ProductImage $existingImage) => $existingImage->getUlid()->equals($image->getUlid())
        );
    }
}
