<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\DTO\CategoryTranslationData;
use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class CategoryManager implements CategoryManagerInterface
{
    public function __construct(
        private CategoryStructureServiceInterface $structureService,
    ) {
    }

    /**
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryParentNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function updateCategory(Category $category, CategoryUpdateData $data): bool
    {
        $newSlug = $data->slug;
        $newParentId = $data->parentId ? Id::fromInt($data->parentId) : null;

        $status = Status::fromString($data->status);
        $translations = $this->mapTranslations($data->translations);
        $adminUlid = AdminUlid::fromString($data->adminUlid);

        $isSlugChanged = $category->isSlugDifferent($newSlug);
        $isParentChanged = $category->isParentDifferent($newParentId);

        $isMoved = $isSlugChanged || $isParentChanged;

        $structureResult = $isMoved
            ? $this->structureService->prepareStructureUpdate($category, $newSlug, $newParentId)
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

    /**
     * @param CategoryTranslationData[] $translations
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapTranslations(array $translations): Translations
    {
        /** @var array<string, array{name?: string, description: ?string}> $mapped */
        $mapped = array_map(fn (CategoryTranslationData $t) => [
            'name' => $t->name,
            'description' => $t->description,
        ], $translations);

        return Translations::fromArray(data: $mapped);
    }
}
