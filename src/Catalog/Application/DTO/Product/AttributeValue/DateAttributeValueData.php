<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class DateAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        public string $value
    ) {
    }
}
