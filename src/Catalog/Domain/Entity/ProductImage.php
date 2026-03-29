<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\ProductImage\Id;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

class ProductImage
{
    public function __construct(
        private readonly Ulid $ulid,
        private RelativeFilePath $path,
        private SortOrder $sortOrder,
        private MainImageFlag $isMain,
        private readonly ?Id $id = null,
    ) {
    }

    public static function create(
        Ulid $ulid,
        RelativeFilePath $path,
        ?SortOrder $sortOrder = null,
        ?MainImageFlag $isMain = null,
    ): self {
        return new self(
            ulid: $ulid,
            path: $path,
            sortOrder: $sortOrder ?? SortOrder::default(),
            isMain: $isMain ?? MainImageFlag::default()
        );
    }

    public function setAsMain(): void
    {
        $this->isMain = MainImageFlag::fromBool(true);
    }

    public function unsetMain(): void
    {
        $this->isMain = MainImageFlag::fromBool(false);
    }

    public function updateSortOrder(SortOrder $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getPath(): RelativeFilePath
    {
        return $this->path;
    }

    public function getSortOrder(): SortOrder
    {
        return $this->sortOrder;
    }

    public function isMain(): MainImageFlag
    {
        return $this->isMain;
    }
}
