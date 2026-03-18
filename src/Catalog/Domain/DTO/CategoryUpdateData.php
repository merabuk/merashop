<?php

declare(strict_types=1);

namespace App\Catalog\Domain\DTO;

use App\Catalog\Domain\ValueObject\Category\Slug;

final readonly class CategoryUpdateData
{
    public function __construct(
        public Slug $slug,
        public ?int $parentId,
        public string $status,
        /** @var array<string, array{name: string, description?: string}> */
        public array $translations,
        public string $adminUlid,
    ) {
    }
}
