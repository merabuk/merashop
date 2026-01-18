<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\CreateModuleAccount;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateModuleAccountCommand implements CommandInterface
{
    public function __construct(
        public string $clientId,
        /**
         * @var array<int, string>
         */
        public array $scopes = [],
    ) {
    }
}
