<?php

namespace App\Shared\Domain\Event;

enum DomainEventNameEnum: string
{
    case USER_REGISTERED = 'integration.users.registered.v1';
}
