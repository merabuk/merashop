<?php

declare(strict_types=1);

namespace App\Catalog\Domain\DTO;

use App\Catalog\Domain\ValueObject\Category\Slug;

final readonly class CategoryUpdateData
{
    /**
     * @param CategoryTranslationData[] $translations
     */
    public function __construct(
        public Slug $slug,
        public ?int $parentId,
        public string $status,
        public array $translations,
        public string $adminUlid,
    ) {
    }
}
