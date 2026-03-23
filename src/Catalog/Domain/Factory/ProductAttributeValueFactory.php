<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Factory\Contract\ProductAttributeValueFactoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\AttributeValueInterface;

final readonly class ProductAttributeValueFactory implements ProductAttributeValueFactoryInterface
{
    /**
     * @throws InvalidAttributeIdException
     * @throws ProductAttributeValueStateException
     * @throws InvalidAttributeOptionIdException
     */
    public function createForTest(
        int $attributeId,
        ?int $attributeOptionId,
        ?AttributeValueInterface $value,
    ): ProductAttributeValue {
        return ProductAttributeValue::create(
            attributeId: AttributeId::fromInt($attributeId),
            attributeOptionId: $attributeOptionId ? AttributeOptionId::fromInt($attributeOptionId) : null,
            value: $value,
        );
    }
}
