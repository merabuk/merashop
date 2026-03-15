<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\ProductAttributeValue;

interface ProductAttributeValueFactoryInterface
{
    public function createForTest(int $attributeId, mixed $value): ProductAttributeValue;
}
