<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\RefreshToken;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidRefreshTokenTokenException extends InvalidIdentityAccessValueObjectException
{
    public static function fromEmptyToken(): self
    {
        return new self('Refresh token cannot be empty.');
    }
}
