<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutgoingEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\Shared\Domain\Exception\InvalidStringException;

class InvalidOutgoingEmailFromNameException extends InvalidEmailSenderValueObjectException
{
    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
