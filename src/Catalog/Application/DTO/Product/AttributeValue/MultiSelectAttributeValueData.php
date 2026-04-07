<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class MultiSelectAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        /**
         * @var int[]
         */
        public array $optionIds,
    ) {
    }
}
