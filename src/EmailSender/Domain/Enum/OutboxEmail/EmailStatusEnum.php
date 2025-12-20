<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Enum\OutboxEmail;

use App\Shared\Domain\Enum\StringEnumTrait;

enum EmailStatusEnum: string
{
    use StringEnumTrait;

    case Created = 'created';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case Sent = 'sent';
}
