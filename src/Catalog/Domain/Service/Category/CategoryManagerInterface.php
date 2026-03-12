<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;

interface CategoryManagerInterface
{
    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryParentNotFoundException
     */
    public function updateCategory(Category $category, CategoryUpdateData $data): bool;
}
