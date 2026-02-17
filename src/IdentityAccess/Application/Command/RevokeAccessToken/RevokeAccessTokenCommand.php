<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\RevokeAccessToken;

use App\Shared\Application\Command\CommandInterface;

class RevokeAccessTokenCommand implements CommandInterface
{
    public function __construct(
        public string $jti,
        public int $expiresAt,
    ) {
    }
}
