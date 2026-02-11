<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryOwnDescendantConflictException;
use App\Catalog\Domain\Exception\Category\CategoryOwnParentConflictException;

class CategoryValidator
{
    /**
     * @throws CategoryOwnParentConflictException
     * @throws CategoryOwnDescendantConflictException
     */
    public function canBeAttachedParent(Category $category, ?Category $newParent): void
    {
        if (null === $newParent) {
            return;
        }

        if ($newParent->getId()->equals($category->getId())) {
            throw new CategoryOwnParentConflictException();
        }

        $categoryPathPrefix = $category->getPath()->value().CategoryPathGenerator::PATH_SEPARATOR;
        if (str_starts_with($newParent->getPath()->value(), $categoryPathPrefix)) {
            throw new CategoryOwnDescendantConflictException();
        }
    }
}
