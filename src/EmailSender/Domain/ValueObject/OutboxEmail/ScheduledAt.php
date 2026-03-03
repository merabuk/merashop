<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class ScheduledAt extends DateTimeValueObject
{
    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    public function isInPast(): bool
    {
        return $this->isBefore(new DateTimeImmutable());
    }
}
