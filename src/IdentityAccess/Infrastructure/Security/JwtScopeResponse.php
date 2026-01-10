<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

final class JwtScopeResponse extends BearerTokenResponse
{
    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        $scopes = array_map(
            static fn (ScopeEntityInterface $scope) => $scope->getIdentifier(),
            $accessToken->getScopes()
        );

        return [
            'scopes' => $scopes,
        ];
    }
}
