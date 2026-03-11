<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class CategoryManager implements CategoryManagerInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryStructureServiceInterface $structureService,
    ) {
    }

    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryParentNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function updateCategory(Category $category, CategoryUpdateData $data): bool
    {
        $newSlug = Slug::fromString($data->slug);
        $newParentId = $data->parentId ? Id::fromInt($data->parentId) : null;

        $status = Status::fromString($data->status);
        $translations = Translations::fromArray($data->translations);
        $adminUlid = AdminUlid::fromString($data->adminUlid);

        $isSlugChanged = $category->isSlugDifferent($newSlug);
        $isParentChanged = $category->isParentDifferent($newParentId);

        if ($isSlugChanged && $this->readRepository->existsBySlug($newSlug, $category->getId())) {
            throw CategoryAlreadyExistsException::becauseSlugAlreadyExists($newSlug->value());
        }

        $isMoved = $isSlugChanged || $isParentChanged;

        $structureResult = $isMoved
            ? $this->structureService->prepareNewStructure($category, $newSlug, $newParentId)
            : null;

        $category->update(
            status: $status,
            translations: $translations,
            updatedBy: $adminUlid,
            newSlug: $isMoved ? $newSlug : null,
            structure: $structureResult
        );

        return $isMoved;
    }
}
