<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

class StatusType extends AbstractPostgresEnumType
{
    public const string NAME = 'outbox_email_status';

    protected function getEnumClass(): string
    {
        return StatusEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
