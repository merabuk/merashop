<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\Shared\Domain\Exception\InvalidEmailAddressException;

final class InvalidOutboxEmailFromException extends InvalidEmailSenderValueObjectException
{
    public static function fromBaseException(InvalidEmailAddressException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
