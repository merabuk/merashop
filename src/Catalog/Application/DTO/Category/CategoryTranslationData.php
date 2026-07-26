<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Category;

final readonly class CategoryTranslationData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {
    }
}
