<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateCategoryCommand implements CommandInterface
{
    public function __construct(
        public string $slug,
        public ?int $parentId,
        public string $status,
        /**
         * @var array<string, array{name: string, description?: string}>
         */
        public array $translations,
    ) {
    }
}
