<?php

namespace App\Customer\Domain\Factory\Contract;

use App\Customer\Domain\Entity\CustomerProfile;

interface CustomerProfileFactoryInterface
{
    public function createForTest(
        string $userUlid,
        string $firstName,
        string $lastName,
        string $phoneNumber,
    ): CustomerProfile;
}
