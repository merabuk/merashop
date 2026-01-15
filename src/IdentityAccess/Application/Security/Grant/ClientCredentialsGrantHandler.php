<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;

class ClientCredentialsGrantHandler implements GrantHandlerInterface
{
    public function supports(GrantTypeEnum $grantType): bool
    {
        return GrantTypeEnum::ClientCredentials === $grantType;
    }

    public function handle(ClientCredentialsInterface $data): TokenResponseData
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
