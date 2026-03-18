<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;

final readonly class CategoryStructureService implements CategoryStructureServiceInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @throws CategoryParentNotFoundException
     * @throws InvalidCategoryPathException
     */
    public function prepareStructure(Slug $slug, ?Id $parentId): CategoryStructureResult
    {
        try {
            $parent = $parentId ? $this->readRepository->getById($parentId) : null;
        } catch (CategoryNotFoundException) {
            throw new CategoryParentNotFoundException();
        }

        $maxSortOrder = $this->readRepository->getMaxSortOrder($parentId);

        return new CategoryStructureResult(
            path: Path::generate($slug, $parent?->getPath()),
            sortOrder: SortOrder::fromInt($maxSortOrder)->next(),
            parentId: $parentId
        );
    }

    /**
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryParentNotFoundException
     * @throws InvalidCategoryPathException
     */
    public function prepareStructureUpdate(Category $category, Slug $newSlug, ?Id $newParentId): CategoryStructureResult
    {
        try {
            $parent = $newParentId ? $this->readRepository->getById($newParentId) : null;
        } catch (CategoryNotFoundException) {
            throw new CategoryParentNotFoundException();
        }

        $category->canBeAttachedTo($parent);

        $newPath = Path::generate($newSlug, $parent?->getPath());

        $newSortOrder = $category->getSortOrder();
        if ($category->isParentDifferent($newParentId)) {
            $maxSort = $this->readRepository->getMaxSortOrder($newParentId);
            $newSortOrder = SortOrder::fromInt($maxSort)->next();
        }

        return new CategoryStructureResult(
            path: $newPath,
            sortOrder: $newSortOrder,
            parentId: $newParentId
        );
    }
}
