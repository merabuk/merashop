<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\StatusEnum;

interface CategoryFactoryInterface
{
    /**
     * @param array<string, array{name: string, description?: string}> $translations
     */
    public function createForTest(
        string $ulid,
        ?int $parentId,
        string $path,
        string $slug,
        int $sortOrder,
        StatusEnum $status,
        array $translations,
        string $createdByUlid,
    ): Category;
}
