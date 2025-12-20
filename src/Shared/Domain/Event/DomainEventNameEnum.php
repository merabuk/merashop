<?php

namespace App\Shared\Domain\Event;

enum DomainEventNameEnum: string
{
    case UserRegistered = 'integration.users.registered.v1';
}
