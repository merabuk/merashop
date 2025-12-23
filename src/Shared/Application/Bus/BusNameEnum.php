<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

enum BusNameEnum: string
{
    case Command = 'command.bus';
    case Event = 'event.bus';
    case Query = 'query.bus';
}
