<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\Product\InvalidProductImageItemException;
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
     */
    public function __construct(array $items)
    {
        try {
            $this->ensureDataType($items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductImageItemException::fromBase($e);
        }
        parent::__construct($items);
    }

    /**
     * @param ProductImage[] $items
     *
     * @throws InvalidProductImageItemException
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
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @throws InvalidProductImageItemException
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
}
