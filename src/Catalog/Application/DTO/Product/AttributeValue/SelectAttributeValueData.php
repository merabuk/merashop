<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class SelectAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        public int $optionId,
    ) {
    }
}
