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
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryParentNotFoundException
     * @throws InvalidCategoryPathException
     */
    public function prepareNewStructure(Category $category, Slug $newSlug, ?Id $newParentId): CategoryStructureResult
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
            newPath: $newPath,
            newSortOrder: $newSortOrder,
            newParentId: $newParentId
        );
    }
}
