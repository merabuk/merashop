<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Exceptions;

use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;

class BadCredentialsException extends IdentityAccessDomainException
{
    public static function becauseUnsupportedGrantType(): self
    {
        return new self('Unsupported grant type');
    }

    public static function becauseInvalidCredentials(?\Throwable $previous = null): self
    {
        return new self('Invalid credentials', previous: $previous);
    }

    public static function becauseInvalidRefreshToken(?\Throwable $previous = null): self
    {
        return new self('Invalid refresh token', previous: $previous);
    }
}
