<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product\AttributeValue;

final readonly class TextAttributeValueData implements AttributeValueDataInterface
{
    public function __construct(
        /**
         * @var array<string, string> [locale => value]
         */
        public array $translations,
    ) {
    }
}
