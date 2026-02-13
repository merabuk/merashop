<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum SharedEventNameEnum: string
{
    case UserRegistered = 'integration.user_registered.v1';
    case AdminCreated = 'integration.admin_created.v1';
}
