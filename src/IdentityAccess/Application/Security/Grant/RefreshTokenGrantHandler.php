<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\RefreshTokenInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;

class RefreshTokenGrantHandler implements GrantHandlerInterface
{
    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::RefreshToken === $grantType;
    }

    public function handle(RefreshTokenInterface $data): TokenResponseData
    {
        // TODO: Implement handle() method.

        return new TokenResponseData(
            accessToken: '',
            refreshToken: '',
            expiresIn: 0,
            tokenType: '',
        );
    }
}
