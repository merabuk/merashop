<?php

declare(strict_types=1);

namespace App\Catalog\Domain\DTO;

final readonly class CategoryTranslationData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {
    }
}
