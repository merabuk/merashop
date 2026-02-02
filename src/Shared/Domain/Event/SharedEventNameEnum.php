<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

enum SharedEventNameEnum: string
{
    case UserRegistered = 'integration.user_registered.v1';
}
