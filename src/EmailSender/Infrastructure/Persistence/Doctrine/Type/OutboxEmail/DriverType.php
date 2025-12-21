<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

class DriverType extends AbstractPostgresEnumType
{
    public const string NAME = 'outbox_email_driver';

    protected function getEnumClass(): string
    {
        return DriverEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
