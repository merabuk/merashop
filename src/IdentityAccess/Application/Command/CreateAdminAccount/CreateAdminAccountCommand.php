<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateAdminAccount;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateAdminAccountCommand implements CommandInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public string $email,
        public string $status,
        public array $roles = [],
    ) {
    }
}
