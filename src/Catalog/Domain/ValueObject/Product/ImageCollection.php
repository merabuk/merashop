<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
use App\Catalog\Domain\Exception\Product\ProductImagesMainImageException;
use App\Catalog\Domain\Exception\Product\ProductImageUniqueException;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;

/**
 * @extends AbstractCollection<ProductImage>
 */
final readonly class ImageCollection extends AbstractCollection
{
    /**
     * @param ProductImage[] $items
     *
     * @throws InvalidProductImageItemException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public function __construct(array $items)
    {
        try {
            $this->ensureDataType($items);
            $this->ensureUnique($items);
            $this->ensureHasMainImage($items);
            parent::__construct($items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductImageItemException::fromBase($e);
        }
    }

    /**
     * @param ProductImage[] $items
     *
     * @throws InvalidProductImageItemException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    public function getByUlid(string|ProductImageUlid $ulid): ?ProductImage
    {
        $ulidValue = $ulid instanceof ProductImageUlid ? $ulid->value() : $ulid;

        return array_find($this->items, fn (ProductImage $item) => $item->getUlid()->value() === $ulidValue);
    }

    /**
     * @throws InvalidProductImageItemException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @throws InvalidProductImageItemException
     * @throws ProductImagesMainImageException
     * @throws ProductImageUniqueException
     */
    public function add(ProductImage $image): self
    {
        if ($this->checkImagesContains($image)) {
            return $this;
        }

        $newItems = $this->items;

        if ($image->isMain()->isTrue()) {
            $newItems = $this->resetMainInArray($newItems);
        }

        if (empty($newItems)) {
            $image->setAsMain();
        }

        $newItems[] = $image;

        return new self($newItems);
    }

    protected function getExpectedClass(): string
    {
        return ProductImage::class;
    }

    /**
     * @param ProductImage[] $items
     *
     * @return ProductImage[]
     */
    private function resetMainInArray(array $items): array
    {
        foreach ($items as $item) {
            $item->unsetMain();
        }

        return $items;
    }

    private function checkImagesContains(ProductImage $image): bool
    {
        return array_any(
            array: $this->items,
            callback: fn (ProductImage $existingImage) => $existingImage->getUlid()->equals($image->getUlid())
        );
    }

    /**
     * @param ProductImage[] $items
     *
     * @throws ProductImageUniqueException
     */
    private function ensureUnique(array $items): void
    {
        $keys = [];
        foreach ($items as $image) {
            if (isset($keys[$image->getUlid()->value()])) {
                throw ProductImageUniqueException::becauseDuplicateImage($image->getUlid()->value());
            }
            $keys[$image->getUlid()->value()] = true;
        }
    }

    /**
     * @param ProductImage[] $items
     *
     * @throws ProductImagesMainImageException
     */
    private function ensureHasMainImage(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $maxMainImagesCount = 1;
        $mainImagesCount = 0;

        foreach ($items as $image) {
            if ($image->isMain()->isTrue()) {
                ++$mainImagesCount;
            }
        }

        if (0 === $mainImagesCount) {
            throw ProductImagesMainImageException::becauseNoMainImage();
        }

        if ($mainImagesCount > $maxMainImagesCount) {
            throw ProductImagesMainImageException::becauseTooManyMainImages($maxMainImagesCount);
        }
    }
}
