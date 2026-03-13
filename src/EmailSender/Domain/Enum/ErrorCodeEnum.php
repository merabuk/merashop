<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Enum;

enum ErrorCodeEnum: string
{
    case EmailSenderDomainError = 'EMAIL_SENDER_DOMAIN_ERROR';
}
