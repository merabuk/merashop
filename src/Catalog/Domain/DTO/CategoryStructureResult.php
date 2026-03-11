<?php

declare(strict_types=1);

namespace App\Catalog\Domain\DTO;

use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\SortOrder;

final readonly class CategoryStructureResult
{
    public function __construct(
        public Path $newPath,
        public SortOrder $newSortOrder,
        public ?Id $newParentId,
    ) {
    }
}
