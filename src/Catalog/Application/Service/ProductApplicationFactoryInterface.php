<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;

interface ProductApplicationFactoryInterface
{
    /**
     * @param int[] $categoryIds
     *
     * @return CategoryId[]
     */
    public function mapCategoryIds(array $categoryIds): array;

    /**
     * @param ProductAttributeValueData[] $attributeValues
     *
     * @return AttributeId[]
     */
    public function mapAttributeIds(array $attributeValues): array;

    public function createFromCommand(CreateProductCommand $command, string $newUlid): Product;

    public function updateFromCommand(Product $product, UpdateProductCommand $command): void;
}
