<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\ValueObject\Category\Path;

interface CategoryWriteRepositoryInterface
{
    public function save(Category $category): Category;

    public function delete(Category $category): void;

    public function replaceOldPathOnNew(Path $oldPath, Path $newPath): void;
}
