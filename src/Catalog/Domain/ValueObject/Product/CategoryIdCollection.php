<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductCategoryIdItemException;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;

/**
 * @extends AbstractCollection<CategoryId>
 */
final readonly class CategoryIdCollection extends AbstractCollection
{
    /**
     * @param CategoryId[] $items
     *
     * @throws InvalidProductCategoryIdItemException
     */
    public function __construct(array $items)
    {
        /* @var array<int, CategoryId> $items */
        try {
            $this->ensureDataType(items: $items);
            parent::__construct(items: $items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductCategoryIdItemException::fromBase($e);
        }
    }

    /**
     * @param CategoryId[] $items
     *
     * @throws InvalidProductCategoryIdItemException
     */
    public static function fromArray(array $items): self
    {
        /* @var array<int, CategoryId> $items */
        return new self(items: $items);
    }

    public function getByCategoryId(int|CategoryId $categoryId): ?CategoryId
    {
        $idValue = $categoryId instanceof CategoryId ? $categoryId->value() : $categoryId;

        return array_find($this->items, fn (CategoryId $item) => $item->value() === $idValue);
    }

    protected function getExpectedClass(): string
    {
        return CategoryId::class;
    }
}
