<?php

namespace App\EmailSender\Domain\Enum\OutgoingEmail;

use App\Shared\Domain\Enum\StringEnumTrait;

enum EmailDriverEnum: string
{
    use StringEnumTrait;

    case Log = 'log';
    case Smtp = 'smtp';
}
