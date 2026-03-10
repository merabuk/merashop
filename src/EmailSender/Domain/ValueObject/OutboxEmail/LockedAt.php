<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateTimeImmutable;

final readonly class LockedAt extends DateTimeValueObject
{
    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }
}
