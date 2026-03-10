<?php

declare(strict_types=1);

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Exception\CustomerProfile\CustomerProfileNotFoundException;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Shared\Domain\ValueObject\Identity\Ulid;

interface CustomerProfileReadRepositoryInterface
{
    public function findById(Id $id): ?CustomerProfile;

    /**
     * @throws CustomerProfileNotFoundException
     */
    public function getByUlid(Ulid $ulid): CustomerProfile;

    public function findByUlid(Ulid $ulid): ?CustomerProfile;

    public function existsByUlid(Ulid $ulid): bool;
}
