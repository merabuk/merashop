<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Ulid;

interface CategoryReadRepositoryInterface
{
    public function findById(Id $id): ?Category;

    public function findByUlid(Ulid $ulid): ?Category;
}
