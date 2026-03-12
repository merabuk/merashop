<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product;

final readonly class ProductTranslationData
{
    public function __construct(
        public string $name,
        public string $description,
    ) {
    }
}
