<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

enum TransportNameEnum: string
{
    // Catalog Module
    case CatalogInternal = 'catalog_internal';
    case CatalogOutbox = 'catalog_outbox';
    case CatalogFailed = 'catalog_failed';
    // Customer Module
    case CustomerExternal = 'customer_external';
    // EmailSender Module
    case EmailSenderExternal = 'email_sender_external';
    case EmailSenderInternal = 'email_sender_internal';
    // IdentityAccess Module
    case IdentityAccessOutbox = 'identity_access_outbox';
    // Shared (Base)
    case AmqpEvents = 'amqp_events';
    case AmqpFailed = 'amqp_failed';
}
