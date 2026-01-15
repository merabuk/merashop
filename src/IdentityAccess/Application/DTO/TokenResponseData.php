<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

final readonly class TokenResponseData
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken,
        public int $expiresIn,
        public string $tokenType = 'Bearer',
    ) {
    }
}
