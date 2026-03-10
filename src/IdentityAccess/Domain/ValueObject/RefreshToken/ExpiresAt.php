<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;

final readonly class ExpiresAt extends DateTimeValueObject
{
    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    public function isExpired(ClockInterface $clock): bool
    {
        return $this->isBefore($clock->now());
    }
}
