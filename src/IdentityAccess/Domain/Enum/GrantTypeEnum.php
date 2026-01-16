<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

enum GrantTypeEnum: string
{
    case Password = 'password';
    case ClientCredentials = 'client_credentials';
    case RefreshToken = 'refresh_token';
    case AuthorizationCode = 'authorization_code';
}
