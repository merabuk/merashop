<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateAdminAccount;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateAdminAccountCommand implements CommandInterface
{
    public function __construct(
        public string $email,
        public string $status,
        /**
         * @var array<int, string>
         */
        public array $roles = [],
    ) {
    }
}
