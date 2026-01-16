<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

final readonly class AccessTokenData
{
    public function __construct(
        public string $token,
        public int $expiresIn,
    ) {
    }
}
