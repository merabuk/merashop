<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryVersionException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Domain\ValueObject\Category\Version;

class Category
{
    public function __construct(
        private readonly Ulid $ulid,
        private ?Id $parentId,
        private Path $path,
        private Slug $slug,
        private SortOrder $sortOrder,
        private Status $status,
        private Translations $translations,
        private Version $version,
        private readonly AdminUlid $createdBy,
        private ?AdminUlid $updatedBy = null,
        private readonly ?Id $id = null,
    ) {
    }

    /**
     * @throws InvalidCategoryVersionException
     */
    public static function create(
        Ulid $ulid,
        ?Id $parentId,
        Path $path,
        Slug $slug,
        SortOrder $sortOrder,
        Status $status,
        Translations $translations,
        AdminUlid $createdBy,
    ): self {
        return new self(
            ulid: $ulid,
            parentId: $parentId,
            path: $path,
            slug: $slug,
            sortOrder: $sortOrder,
            status: $status,
            translations: $translations,
            version: Version::initial(),
            createdBy: $createdBy,
        );
    }

    public function update(
        Status $status,
        Translations $translations,
        AdminUlid $updatedBy,
        ?Slug $newSlug = null,
        ?CategoryStructureResult $structure = null,
    ): void {
        $this->status = $status;
        $this->translations = $translations;
        $this->updatedBy = $updatedBy;

        if (null !== $structure && null !== $newSlug) {
            $this->slug = $newSlug;
            $this->path = $structure->newPath;
            $this->sortOrder = $structure->newSortOrder;
            $this->parentId = $structure->newParentId;
        }
    }

    public function isSlugDifferent(Slug $slug): bool
    {
        return false === $this->slug->equals($slug);
    }

    public function isParentDifferent(?Id $parentId): bool
    {
        return (string) $this->parentId !== (string) $parentId;
    }

    /**
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryChildCanNotBeParentConflictException
     */
    public function canBeAttachedTo(?Category $potentialParent): void
    {
        if (null === $potentialParent) {
            return;
        }

        if ($this->id && $potentialParent->getId()->equals($this->id)) {
            throw new CategoryCannotBeParentOfItselfException();
        }

        if ($potentialParent->getPath()->startsWith($this->path)) {
            throw new CategoryChildCanNotBeParentConflictException();
        }
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getParentId(): ?Id
    {
        return $this->parentId;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getSortOrder(): SortOrder
    {
        return $this->sortOrder;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getTranslations(): Translations
    {
        return $this->translations;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getCreatedBy(): AdminUlid
    {
        return $this->createdBy;
    }

    public function getUpdatedBy(): ?AdminUlid
    {
        return $this->updatedBy;
    }

    public function updateSortOrder(SortOrder $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }
}
