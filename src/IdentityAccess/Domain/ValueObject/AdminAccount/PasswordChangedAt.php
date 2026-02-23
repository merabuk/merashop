<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\Shared\Domain\ValueObject\DateTimeValueObject;
use DateTimeImmutable;

final readonly class PasswordChangedAt extends DateTimeValueObject
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
