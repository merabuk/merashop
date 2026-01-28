<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\RefreshToken;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectExceptionInterface;

final class InvalidRefreshTokenAccountTypeException extends InvalidIdentityAccessValueObjectExceptionInterface
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidAccountType(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Refresh token account type. Available statuses: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
