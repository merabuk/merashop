<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product;

final readonly class ProductAttributeValueData
{
    public function __construct(
        public int $attributeId,
        public mixed $value,
    ) {
    }
}
