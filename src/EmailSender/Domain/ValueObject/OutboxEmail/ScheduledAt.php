<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\ValueObject\OutboxEmail;

use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class ScheduledAt extends DateTimeValueObject
{
    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public static function now(ClockInterface $clock): self
    {
        return new self($clock->now());
    }

    public function isInPast(ClockInterface $clock): bool
    {
        return $this->isBefore($clock->now());
    }
}
