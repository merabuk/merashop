<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateProductCommand implements CommandInterface
{
    /**
     * @param ProductPriceData[]          $prices
     * @param int[]                       $categoryIds
     * @param ProductAttributeValueData[] $attributeValues
     * @param ProductTranslationData[]    $translations
     * @param string[]                    $images
     */
    public function __construct(
        public string $sku,
        public string $status,
        public array $prices,
        public array $categoryIds,
        public array $attributeValues,
        public array $translations,
        public array $images,
        public string $adminUlid,
    ) {
    }
}
