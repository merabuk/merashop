<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;

interface CategoryStructureServiceInterface
{
    /**
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryParentNotFoundException
     */
    public function prepareNewStructure(Category $category, Slug $newSlug, ?Id $newParentId): CategoryStructureResult;
}
