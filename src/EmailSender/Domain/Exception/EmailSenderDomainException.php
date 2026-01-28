<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

use App\Shared\Domain\Exception\LogicException;

abstract class EmailSenderDomainException extends LogicException implements EmailSenderExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'EMAIL_SENDER_DOMAIN_ERROR';
    }
}
