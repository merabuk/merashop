<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

use App\Shared\Domain\Exception\ValueObjectExceptionInterface;

abstract class InvalidEmailSenderValueObjectExceptionInterface extends EmailSenderDomainException implements ValueObjectExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'INVALID_EMAIL_SENDER_VALUE_OBJECT';
    }
}
