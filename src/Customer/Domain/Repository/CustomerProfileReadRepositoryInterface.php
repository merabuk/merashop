<?php

declare(strict_types=1);

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Shared\Domain\ValueObject\Ulid;

interface CustomerProfileReadRepositoryInterface
{
    public function findById(int $id): ?CustomerProfile;

    public function findByUlid(Ulid $ulid): ?CustomerProfile;

    public function existsByUlid(Ulid $ulid): bool;
}
