<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\RefreshToken;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidRefreshTokenTokenHashException extends InvalidIdentityAccessValueObjectException
{
    public static function fromEmptyTokenHash(): self
    {
        return new self('Refresh token hash cannot be empty');
    }
}
