<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Shared\Application\Command\CommandInterface;

class UpdateProductCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $status,
        /**
         * @var ProductPriceData[]
         */
        public array $prices,
        /**
         * @var int[]
         */
        public array $categoryIds,
        /**
         * @var ProductAttributeValueData[]
         */
        public array $attributeValues,
        /**
         * @var ProductTranslationData[]
         */
        public array $translations,
        /**
         * @var string[]
         */
        public array $images,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
