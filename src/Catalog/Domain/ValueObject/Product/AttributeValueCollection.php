<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Product\InvalidProductAttributeValueItemException;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;

/**
 * @extends AbstractCollection<ProductAttributeValue>
 */
final readonly class AttributeValueCollection extends AbstractCollection
{
    /**
     * @param ProductAttributeValue[] $items
     *
     * @throws InvalidProductAttributeValueItemException
     */
    public function __construct(array $items)
    {
        try {
            $this->ensureDataType($items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductAttributeValueItemException::fromBase($e);
        }
        parent::__construct($items);
    }

    /**
     * @param ProductAttributeValue[] $items
     *
     * @throws InvalidProductAttributeValueItemException
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    protected function getExpectedClass(): string
    {
        return ProductAttributeValue::class;
    }
}
