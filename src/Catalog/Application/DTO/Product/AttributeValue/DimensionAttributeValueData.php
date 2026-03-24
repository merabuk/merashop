<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class DimensionAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        public float $magnitude,
        public int $unitOptionId,
    ) {
    }
}
