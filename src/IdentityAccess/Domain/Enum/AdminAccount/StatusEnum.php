<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Enum\AdminAccount;

use App\Shared\Domain\Enum\StringEnumTrait;

enum StatusEnum: string
{
    use StringEnumTrait;

    case Active = 'active';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
    case Deleted = 'deleted';
    case Draft = 'draft';
    case OnVacation = 'on_vacation';
}
