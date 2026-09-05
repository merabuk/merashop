<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Shared\Application\Command\CommandInterface;

class UpdateProductCommand implements CommandInterface
{
    /**
     * @param array<int, ProductPriceData> $prices
     * @param int[]                        $categoryIds
     * @param ProductAttributeValueData[]  $attributeValues
     * @param ProductTranslationData[]     $translations
     * @param string[]                     $images
     */
    public function __construct(
        public int $id,
        public string $sku,
        public string $status,
        public array $prices,
        public array $categoryIds,
        public array $attributeValues,
        public array $translations,
        public array $images,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
