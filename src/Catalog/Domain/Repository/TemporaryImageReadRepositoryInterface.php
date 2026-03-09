<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;

interface TemporaryImageReadRepositoryInterface
{
    public function findById(Id $id): ?TemporaryImage;

    public function findByUlid(Ulid $ulid): ?TemporaryImage;
}
