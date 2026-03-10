<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

use DateTimeImmutable;

interface EqualsWithDateTimeInterface
{
    public function value(): DateTimeImmutable;

    public function equalsWithDateTime(DateTimeImmutable $other): bool;

    public function isAfter(self|DateTimeImmutable $other): bool;

    public function isBefore(self|DateTimeImmutable $other): bool;
}
