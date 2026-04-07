<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Attribute;

final readonly class AttributeOptionTranslationData
{
    public function __construct(
        public string $value,
    ) {
    }
}
