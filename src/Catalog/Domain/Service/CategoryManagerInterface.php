<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;

interface CategoryManagerInterface
{
    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryCannotBeParentOfItselfException
     */
    public function updateCategory(Category $category, CategoryUpdateData $data): bool;
}
