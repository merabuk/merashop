<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutgoingEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

class InvalidOutgoingEmailDriverException extends InvalidEmailSenderValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidDriver(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Outgoing email driver. Available drivers: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
