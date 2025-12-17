<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutgoingEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

class InvalidOutgoingEmailIdException extends InvalidEmailSenderValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Outgoing email ID');
    }
}
