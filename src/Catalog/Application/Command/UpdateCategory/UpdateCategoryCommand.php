<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Shared\Application\Command\CommandInterface;

class UpdateCategoryCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $slug,
        public ?int $parentId,
        public int $sortOrder,
        public string $status,
        /**
         * @var array<string, array{name: string, description?: string}>
         */
        public array $translations,
    ) {
    }
}
