<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\AdminAccount;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

class StatusType extends AbstractPostgresEnumType
{
    public const string NAME = 'admin_account_status';

    protected function getEnumClass(): string
    {
        return StatusEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
