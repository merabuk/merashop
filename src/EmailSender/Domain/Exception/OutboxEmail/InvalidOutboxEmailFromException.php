<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectExceptionInterface;
use App\Shared\Domain\Exception\InvalidEmailAddressException;

final class InvalidOutboxEmailFromException extends InvalidEmailSenderValueObjectExceptionInterface
{
    public static function fromBaseException(InvalidEmailAddressException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
