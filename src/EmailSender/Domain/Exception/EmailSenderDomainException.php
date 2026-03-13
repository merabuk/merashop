<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

use App\EmailSender\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\LogicException;

abstract class EmailSenderDomainException extends LogicException implements EmailSenderExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::EmailSenderDomainError->value;
    }

    public function getTranslationDomain(): string
    {
        return 'email_sender_exceptions';
    }
}
