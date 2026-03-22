<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Attribute;

final readonly class AttributeTranslationData
{
    public function __construct(
        public string $name,
    ) {
    }
}
