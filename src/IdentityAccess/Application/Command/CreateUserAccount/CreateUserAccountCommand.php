<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateUserAccount;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateUserAccountCommand implements CommandInterface
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
