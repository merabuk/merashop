<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Enum;

enum ErrorCodeEnum: string
{
    case EmailSenderDomainError = 'email_sender_domain_error';
}
