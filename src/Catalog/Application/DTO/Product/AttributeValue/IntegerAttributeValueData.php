<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class IntegerAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        public int $value
    ) {
    }
}
