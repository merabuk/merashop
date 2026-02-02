<?php

declare(strict_types=1);

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\CustomerProfile;

interface CustomerProfileWriteRepositoryInterface
{
    public function save(CustomerProfile $customerProfile): CustomerProfile;

    public function delete(CustomerProfile $customerProfile): void;
}
