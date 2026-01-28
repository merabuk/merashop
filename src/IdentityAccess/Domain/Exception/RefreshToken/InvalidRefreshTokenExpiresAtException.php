<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\RefreshToken;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;

final class InvalidRefreshTokenExpiresAtException extends InvalidIdentityAccessValueObjectExceptionInterface
{
    public static function fromPastDate(): self
    {
        return new self('Expiration date cannot be in the past.');
    }
}
