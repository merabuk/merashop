<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Ulid;

interface CategoryReadRepositoryInterface
{
    /**
     * @throws CategoryNotFoundException
     */
    public function getById(Id $id, bool $withParent = true, bool $withTranslations = true): Category;

    public function findById(Id $id, bool $withParent = true, bool $withTranslations = true): ?Category;

    public function findByUlid(Ulid $ulid, bool $withParent = true, bool $withTranslations = true): ?Category;

    public function getMaxSortOrder(?Id $parentId): int;

    /**
     * @param Id[] $ids
     *
     * @throws OneOfCategoriesNotFoundException
     */
    public function assertAllExistByIds(array $ids): void;

    public function existsBySlug(Slug $slug, ?Id $excludeId = null): bool;
}
