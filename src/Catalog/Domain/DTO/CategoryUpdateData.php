<?php

declare(strict_types=1);

namespace App\Catalog\Domain\DTO;

final readonly class CategoryUpdateData
{
    public function __construct(
        public string $slug,
        public ?int $parentId,
        public string $status,
        /** @var array<string, array{name: string, description?: string}> */
        public array $translations,
        public string $adminUlid,
    ) {
    }
}
