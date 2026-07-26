<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\Exception\Category\InvalidCategorySlugException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryUlidException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryVersionException;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Factory\Contract\CategoryFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class CategoryFactory implements CategoryFactoryInterface
{
    /**
     * @param array<string, array{name?: string, description: ?string}> $translations
     *
     * @throws InvalidAdminUlidException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidCategoryIdException
     * @throws InvalidCategoryPathException
     * @throws InvalidCategorySlugException
     * @throws InvalidCategoryUlidException
     * @throws InvalidCategoryVersionException
     * @throws InvalidLocaleException
     */
    public function createForTest(
        string $ulid,
        ?int $parentId,
        string $path,
        string $slug,
        int $sortOrder,
        StatusEnum $status,
        array $translations,
        string $createdByUlid,
    ): Category {
        return Category::create(
            ulid: Ulid::fromString($ulid),
            parentId: $parentId ? Id::fromInt($parentId) : null,
            path: Path::fromString($path),
            slug: Slug::fromString($slug),
            sortOrder: SortOrder::fromInt($sortOrder),
            status: Status::fromEnum($status),
            translations: Translations::fromArray($translations),
            createdBy: AdminUlid::fromString($createdByUlid),
        );
    }
}
