<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Catalog\Domain\DTO\CategoryTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateCategoryCommand implements CommandInterface
{
    /**
     * @param CategoryTranslationData[] $translations
     */
    public function __construct(
        public string $slug,
        public ?int $parentId,
        public string $status,
        public array $translations,
        public string $adminUlid,
    ) {
    }
}
