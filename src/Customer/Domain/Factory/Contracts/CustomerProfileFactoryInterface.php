<?php

namespace App\Customer\Domain\Factory\Contracts;

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
