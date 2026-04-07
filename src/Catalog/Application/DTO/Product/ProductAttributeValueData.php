<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;

final readonly class ProductAttributeValueData
{
    public function __construct(
        public int $attributeId,
        public AttributeValueDataInterface $value,
    ) {
    }
}
