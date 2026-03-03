<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

final class InvalidOutboxEmailBodyException extends InvalidEmailSenderValueObjectException
{
    public static function becauseItEmpty(): self
    {
        return new self('The outbox email body cannot be empty');
    }
}
