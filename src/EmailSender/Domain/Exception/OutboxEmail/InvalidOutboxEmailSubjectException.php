<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\Shared\Domain\Exception\InvalidStringException;

class InvalidOutboxEmailSubjectException extends InvalidEmailSenderValueObjectException
{
    public static function fromBaseException(InvalidStringException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
