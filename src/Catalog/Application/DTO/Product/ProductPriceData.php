<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Product;

final readonly class ProductPriceData
{
    public function __construct(
        public int $amount,
        public string $currency,
        public string $type,
        public float $taxValue,
        public string $taxType,
        public bool $taxIncluded,
        public ?string $validFrom,
        public ?string $validTo,
    ) {
    }
}
