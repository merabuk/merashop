<?php

declare(strict_types=1);

namespace App\Customer\Application\Command\CreateCustomerProfile;

use App\Shared\Application\Command\CommandInterface;

class CreateCustomerProfileCommand implements CommandInterface
{
    public function __construct(
        public string $userUlid,
    ) {
    }
}
