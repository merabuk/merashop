<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;

final readonly class ProductDataFactory
{
    public function __construct(
        private CategoryReadRepositoryInterface $categoryReadRepository,
        private AttributeReadRepositoryInterface $attributeReadRepository,
    ) {
    }

    /**
     * @param int[] $rawCategoryIds
     *
     * @return CategoryId[]
     *
     * @throws InvalidCategoryIdException
     * @throws OneOfCategoriesNotFoundException
     */
    public function prepareCategories(array $rawCategoryIds): array
    {
        $categoryIds = array_map(fn (int $id) => CategoryId::fromInt($id), $rawCategoryIds);
        $this->categoryReadRepository->assertAllExistByIds($categoryIds);

        return $categoryIds;
    }

    /**
     * @param array<int, array{attributeId: int, value: mixed}> $rawAttributes
     *
     * @return ProductAttributeValue[]
     *
     * @throws InvalidAttributeIdException
     * @throws OneOfAttributesNotFoundException
     */
    public function prepareAttributes(array $rawAttributes): array
    {
        $attributeValues = [];
        $attributeIds = [];

        foreach ($rawAttributes as $item) {
            $attrId = AttributeId::fromInt($item['attributeId']);
            $attributeIds[] = $attrId;
            $attributeValues[] = ProductAttributeValue::createWithRawValue($attrId, $item['value']);
        }

        $this->attributeReadRepository->assertAllExistByIds($attributeIds);

        return $attributeValues;
    }
}
