<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\ProductImage;

interface ProductImageFactoryInterface
{
    public function createForTest(
        string $ulid,
        string $path,
        int $sortOrder,
        bool $isMain,
    ): ProductImage;
}
