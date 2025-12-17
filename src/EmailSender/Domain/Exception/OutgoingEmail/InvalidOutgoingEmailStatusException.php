<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutgoingEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

class InvalidOutgoingEmailStatusException extends InvalidEmailSenderValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidStatus(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Outgoing email status. Available statuses: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
