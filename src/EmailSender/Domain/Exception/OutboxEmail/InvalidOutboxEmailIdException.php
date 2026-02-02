<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

final class InvalidOutboxEmailIdException extends InvalidEmailSenderValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Outgoing email ID');
    }
}
