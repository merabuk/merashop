<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\RefreshToken;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidRefreshTokenTokenHashException extends InvalidIdentityAccessValueObjectException
{
    public static function becauseItEmpty(): self
    {
        return new self('Refresh token hash cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Refresh token hash cannot be longer than %d characters', $maxLength));
    }
}
