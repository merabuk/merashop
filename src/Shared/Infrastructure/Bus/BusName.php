<?php

namespace App\Shared\Infrastructure\Bus;

enum BusName: string
{
    case Command = 'command.bus';
    case Query = 'query.bus';
}
