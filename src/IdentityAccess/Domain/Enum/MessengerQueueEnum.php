<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum;

enum MessengerQueueEnum: string
{
    case Outbox = 'outbox';
    case Failed = 'failed';
}
