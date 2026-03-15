<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;

interface ProductFactoryInterface
{
    /**
     * @param array<string, array{name: string, description?: string}> $translations
     * @param ProductPrice[]                                           $prices
     * @param CategoryId[]                                             $categoryIds
     * @param ProductAttributeValue[]                                  $attributeValues
     */
    public function createForTest(
        string $ulid,
        string $sku,
        StatusEnum $status,
        array $translations,
        array $prices,
        string $createdByUlid,
        array $categoryIds,
        array $attributeValues,
    ): Product;
}
