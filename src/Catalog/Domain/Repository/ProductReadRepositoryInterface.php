<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Exception\Product\ProductNotFoundException;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Ulid;

interface ProductReadRepositoryInterface
{
    /**
     * @throws ProductNotFoundException
     */
    public function getById(Id $id): Product;

    public function findById(Id $id): ?Product;

    public function findByUlid(Ulid $ulid): ?Product;

    public function existsBySku(Sku $sku): bool;
}
