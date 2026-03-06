<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Shared\Application\Command\CommandInterface;

class UpdateProductCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $status,
        public int $priceAmount,
        public string $priceCurrency,
        /**
         * @var int[]
         */
        public array $categoryIds,
        /**
         * @var array<int, array{attributeId: int, value: mixed}>
         */
        public array $attributeValues,
        /**
         * @var array<string, array{name: string, description?: string}>
         */
        public array $translations,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
