<?php

namespace App\EmailSender\Domain\Enum\OutboxEmail;

use App\Shared\Domain\Enum\StringEnumTrait;

enum DriverEnum: string
{
    use StringEnumTrait;

    case Log = 'log';
    case Smtp = 'smtp';
}
