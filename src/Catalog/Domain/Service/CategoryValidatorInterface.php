<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;

interface CategoryValidatorInterface
{
    /**
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryChildCanNotBeParentConflictException
     */
    public function canBeAttachedParent(Category $category, ?Category $newParent): void;
}
