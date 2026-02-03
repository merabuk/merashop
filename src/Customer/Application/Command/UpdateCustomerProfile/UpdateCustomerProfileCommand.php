<?php

declare(strict_types=1);

namespace App\Customer\Application\Command\UpdateCustomerProfile;

use App\Shared\Application\Command\CommandInterface;

class UpdateCustomerProfileCommand implements CommandInterface
{
    public function __construct(
        public string $userUlid,
        public string $firstName,
        public string $lastName,
        public string $phoneNumber,
    ) {
    }
}
