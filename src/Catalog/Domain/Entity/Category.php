<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;

class Category
{
    public function __construct(
        private readonly ?Id $id,
        private readonly Ulid $ulid,
        private ?Id $parentId,
        private Path $path,
        private Slug $slug,
        private SortOrder $sortOrder,
        private Status $status,
        private Translations $translations,
    ) {
    }

    public static function create(
        Ulid $ulid,
        ?Id $parentId,
        Path $path,
        Slug $slug,
        SortOrder $sortOrder,
        Status $status,
        Translations $translations,
    ): self {
        return new self(
            id: null,
            ulid: $ulid,
            parentId: $parentId,
            path: $path,
            slug: $slug,
            sortOrder: $sortOrder,
            status: $status,
            translations: $translations,
        );
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

    public function update(
        ?Id $parentId,
        Path $path,
        Slug $slug,
        SortOrder $sortOrder,
        Status $status,
        Translations $translations,
    ): void {
        $this->parentId = $parentId;
        $this->path = $path;
        $this->slug = $slug;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->translations = $translations;
    }
}
