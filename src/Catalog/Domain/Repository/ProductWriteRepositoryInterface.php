<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Product;

interface ProductWriteRepositoryInterface
{
    public function save(Product $product): Product;

    public function delete(Product $product): void;
}
