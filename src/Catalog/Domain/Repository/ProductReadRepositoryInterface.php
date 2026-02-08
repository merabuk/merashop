<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\ValueObject\Product\Id;
use App\Catalog\Domain\ValueObject\Product\Ulid;

interface ProductReadRepositoryInterface
{
    public function findById(Id $id): ?Product;

    public function findByUlid(Ulid $ulid): ?Product;
}
