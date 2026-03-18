<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Repository\ProductWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;

final readonly class ProductFixture
{
    public function __construct(
        private ProductMother $mother,
        private ProductWriteRepositoryInterface $repository,
    ) {
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     * @param ?ProductPrice[]                                           $prices
     * @param ?CategoryId[]                                             $categoryIds
     * @param ?ProductAttributeValue[]                                  $attributeValues
     * @param ?ProductImage[]                                           $images
     */
    public function create(
        ?string $ulid = null,
        ?string $sku = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?array $prices = null,
        ?string $createdByUlid = null,
        ?array $categoryIds = null,
        ?array $attributeValues = null,
        ?array $images = null,
    ): Product {
        $category = $this->mother->create(
            ulid: $ulid,
            sku: $sku,
            status: $status,
            translations: $translations,
            prices: $prices,
            createdByUlid: $createdByUlid,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
            images: $images,
        );

        return $this->repository->save($category);
    }

    /**
     * @return Product[]
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
