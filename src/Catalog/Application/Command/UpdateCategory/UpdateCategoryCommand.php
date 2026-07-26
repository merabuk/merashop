<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Catalog\Application\DTO\Category\CategoryTranslationData;
use App\Shared\Application\Command\CommandInterface;

class UpdateCategoryCommand implements CommandInterface
{
    /**
     * @param CategoryTranslationData[] $translations
     */
    public function __construct(
        public int $id,
        public string $slug,
        public ?int $parentId,
        public string $status,
        public array $translations,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
