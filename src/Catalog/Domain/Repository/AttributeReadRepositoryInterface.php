<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;

interface AttributeReadRepositoryInterface
{
    public function findById(Id $id): ?Attribute;

    public function findByUlid(Ulid $ulid): ?Attribute;
}
