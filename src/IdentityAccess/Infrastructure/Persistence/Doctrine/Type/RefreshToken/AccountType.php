<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Persistence\Doctrine\Type\RefreshToken;

use App\IdentityAccess\Domain\Enum\AccountTypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

class AccountType extends AbstractPostgresEnumType
{
    public const string NAME = 'refresh_token_account_type';

    protected function getEnumClass(): string
    {
        return AccountTypeEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
