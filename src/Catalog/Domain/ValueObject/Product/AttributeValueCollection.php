<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Product\InvalidProductAttributeValueItemException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
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
            parent::__construct($items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductAttributeValueItemException::fromBase($e);
        }
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

    public function getByAttributeId(int|AttributeId $attributeId): ?ProductAttributeValue
    {
        $idValue = $attributeId instanceof AttributeId ? $attributeId->value() : $attributeId;

        return array_find(
            $this->items,
            fn (ProductAttributeValue $item) => $item->getAttributeId()->value() === $idValue
        );
    }

    public function findByBusinessKey(AttributeId $attributeId, ?AttributeOptionId $attributeOptionId): ?ProductAttributeValue
    {
        return array_find($this->items, function (ProductAttributeValue $pav) use ($attributeId, $attributeOptionId) {
            if (!$pav->getAttributeId()->equals($attributeId)) {
                return false;
            }

            if (null !== $attributeOptionId && null !== $pav->getAttributeOptionId()) {
                return $pav->getAttributeOptionId()->equals($attributeOptionId);
            }

            return null === $pav->getAttributeOptionId();
        });
    }

    protected function getExpectedClass(): string
    {
        return ProductAttributeValue::class;
    }
}
