<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

class InvalidOutboxEmailDriverException extends InvalidEmailSenderValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidDriver(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Outbox email driver. Available drivers: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
