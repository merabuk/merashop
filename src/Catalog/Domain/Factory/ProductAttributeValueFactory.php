<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Factory\Contract\ProductAttributeValueFactoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;

final readonly class ProductAttributeValueFactory implements ProductAttributeValueFactoryInterface
{
    /**
     * @throws InvalidAttributeIdException
     */
    public function createForTest(
        int $attributeId,
        mixed $value,
    ): ProductAttributeValue {
        return ProductAttributeValue::create(
            attributeId: AttributeId::fromInt($attributeId),
            value: ProductAttributeValue::resolveValue($value),
        );
    }
}
