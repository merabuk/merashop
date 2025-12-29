<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

enum SharedEventNameEnum: string
{
    case UserRegistered = 'integration.users.registered.v1';
}
