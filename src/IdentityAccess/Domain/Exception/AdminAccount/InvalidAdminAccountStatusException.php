<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception\AdminAccount;

use App\IdentityAccess\Domain\Exception\InvalidIdentityAccessValueObjectException;

final class InvalidAdminAccountStatusException extends InvalidIdentityAccessValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidStatus(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Admin account status. Available statuses: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
