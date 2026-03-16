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
        try {
            $this->ensureDataType($items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductCategoryIdItemException::fromBase($e);
        }
        parent::__construct($items);
    }

    /**
     * @param CategoryId[] $items
     *
     * @throws InvalidProductCategoryIdItemException
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    protected function getExpectedClass(): string
    {
        return CategoryId::class;
    }
}
