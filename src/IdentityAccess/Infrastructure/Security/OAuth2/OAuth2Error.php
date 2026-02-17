<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\OAuth2;

final class OAuth2Error
{
    public const string INVALID_REQUEST = 'invalid_request';
    public const string INVALID_CLIENT = 'invalid_client';
    public const string INVALID_GRANT = 'invalid_grant';
    public const string UNSUPPORTED_GRANT_TYPE = 'unsupported_grant_type';
    public const string SERVER_ERROR = 'server_error';

    public static function getDescription(string $errorCode): string
    {
        return match ($errorCode) {
            self::INVALID_CLIENT => 'Client authentication failed',
            self::INVALID_GRANT => 'The provided authorization grant is invalid, expired, or revoked',
            self::UNSUPPORTED_GRANT_TYPE => 'The authorization grant type is not supported',
            default => 'An unexpected error occurred',
        };
    }
}
