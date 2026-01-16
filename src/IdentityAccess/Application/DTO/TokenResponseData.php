<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

final readonly class TokenResponseData
{
    public function __construct(
        public AccessTokenData $accessTokenData,
        public ?RefreshTokenData $refreshTokenData = null,
    ) {
    }
}
