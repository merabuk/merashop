<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Enum;

enum EmailSenderEventNameEnum: string
{
    case EmailProcessor = 'email_sender.processor.v1';
}
