<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryMoveToChildConflictException;

final readonly class CategoryValidator implements CategoryValidatorInterface
{
    /**
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryMoveToChildConflictException
     */
    public function canBeAttachedParent(Category $category, ?Category $newParent): void
    {
        if (null === $newParent) {
            return;
        }

        if ($newParent->getId()->equals($category->getId())) {
            throw new CategoryCannotBeParentOfItselfException();
        }

        if ($newParent->getPath()->startsWith($category->getPath())) {
            throw new CategoryMoveToChildConflictException();
        }
    }
}
