<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryMoveToChildConflictException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class CategoryManager implements CategoryManagerInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryValidatorInterface $validator,
    ) {
    }

    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryMoveToChildConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function updateCategory(Category $category, CategoryUpdateData $data): bool
    {
        $newSlug = Slug::fromString($data->slug);
        $newParentId = $data->parentId ? Id::fromInt($data->parentId) : null;

        $isMoved = $this->updateCategoryStructure($category, $newSlug, $newParentId);

        $category->update(
            status: Status::fromString($data->status),
            translations: Translations::fromArray($data->translations),
            updatedBy: AdminUlid::fromString($data->adminUlid)
        );

        return $isMoved;
    }

    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryMoveToChildConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws InvalidCategoryPathException
     */
    private function updateCategoryStructure(
        Category $category,
        Slug $newSlug,
        ?Id $newParentId,
    ): bool {
        $slugChanged = $this->checkAndUpdateSlug($category, $newSlug);

        $parentChanged = $this->isParentChanged($category, $newParentId);

        $parent = $newParentId ? $this->readRepository->findById($newParentId) : null;

        $this->validator->canBeAttachedParent($category, $parent);

        if ($parentChanged || $slugChanged) {
            $category->updatePath(Path::generate($newSlug, $parent?->getPath()));
        }

        if ($parentChanged) {
            $maxSort = $this->readRepository->getMaxSortOrder($newParentId);
            $category->updateSortOrder(SortOrder::fromInt($maxSort)->next());
            $category->updateParentId($newParentId);
        }

        return $parentChanged || $slugChanged;
    }

    /**
     * @throws CategoryAlreadyExistsException
     */
    private function checkAndUpdateSlug(Category $category, Slug $newSlug): bool
    {
        $slugChanged = !$category->getSlug()->equals($newSlug);

        if ($slugChanged) {
            !$this->readRepository->existsBySlug($newSlug) ?: throw new CategoryAlreadyExistsException();

            $category->updateSlug($newSlug);
        }

        return $slugChanged;
    }

    private function isParentChanged(Category $category, ?Id $newParentId): bool
    {
        if (null === $category->getParentId() && null === $newParentId) {
            return false;
        }

        if (null === $category->getParentId() || null === $newParentId) {
            return true;
        }

        return !$category->getParentId()->equals($newParentId);
    }
}
