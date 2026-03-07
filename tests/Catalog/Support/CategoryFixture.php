<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;

final readonly class CategoryFixture
{
    public function __construct(
        private CategoryMother $mother,
        private CategoryWriteRepositoryInterface $repository,
    ) {
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     */
    public function create(
        ?string $ulid = null,
        ?int $parentId = null,
        ?string $path = null,
        ?string $slug = null,
        ?int $sortOrder = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?string $createdByUlid = null,
    ): Category {
        $category = $this->mother->create(
            ulid: $ulid,
            parentId: $parentId,
            path: $path,
            slug: $slug,
            sortOrder: $sortOrder,
            status: $status,
            translations: $translations,
            createdByUlid: $createdByUlid,
        );

        return $this->repository->save($category);
    }

    /**
     * @return Category[]
     */
    public function createMany(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->create();
        }

        return $items;
    }
}
