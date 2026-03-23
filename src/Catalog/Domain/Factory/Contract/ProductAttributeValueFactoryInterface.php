<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Value\AttributeValueInterface;

interface ProductAttributeValueFactoryInterface
{
    public function createForTest(
        int $attributeId,
        ?int $attributeOptionId,
        ?AttributeValueInterface $value,
    ): ProductAttributeValue;
}
