<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectExceptionInterface;

final class InvalidOutboxEmailIdException extends InvalidEmailSenderValueObjectExceptionInterface
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid Outgoing email ID');
    }
}
