<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Enum\OutboxEmail;

use App\Shared\Domain\Enum\StringEnumTrait;

enum StatusEnum: string
{
    use StringEnumTrait;

    case Created = 'created';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';
    case FailedPermanently = 'failed_permanently';
}
