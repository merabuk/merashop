<?php

namespace App\Shared\Application\Bus;

enum BusName: string
{
    case Command = 'command.bus';
    case Query = 'query.bus';
}
