<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class EmailSenderDomainException extends ServerException implements ThrowableEmailSenderException
{
    public function getErrorCode(): string
    {
        return 'EMAIL_SENDER_DOMAIN_ERROR';
    }
}
