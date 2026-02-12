<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum\AdminAccount;

enum StatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
    case Deleted = 'deleted';
    case Draft = 'draft';
    case Vacation = 'vacation';
}
