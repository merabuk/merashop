<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

enum TransportNameEnum: string
{
    case IdentityAccessOutbox = 'identity_access_outbox';
    case AmqpEvents = 'amqp_events';
    case AmqpInternal = 'amqp_internal';
}
