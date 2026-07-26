<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Temporal;

use App\Shared\Domain\ValueObject\Contract\EqualsWithDateTimeInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;
use DateTimeImmutable;

abstract readonly class AbstractTemporalValueObject implements EqualsWithDateTimeInterface, ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const string COMPARISON_FORMAT = 'Y-m-d H:i:s.u';
    public const string OUTPUT_FORMAT = DateTimeImmutable::ATOM;

    protected DateTimeImmutable $date;

    protected function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    public function value(): DateTimeImmutable
    {
        return $this->date;
    }

    public function __toString(): string
    {
        return $this->date->format(static::OUTPUT_FORMAT);
    }

    protected function getPrimitiveValue(): string
    {
        return $this->date->format(static::COMPARISON_FORMAT);
    }

    public function equalsWithDateTime(EqualsWithDateTimeInterface|DateTimeImmutable $other): bool
    {
        $otherDate = $other instanceof EqualsWithDateTimeInterface ? $other->value() : $other;

        return $this->date->format(static::COMPARISON_FORMAT) === $otherDate->format(static::COMPARISON_FORMAT);
    }

    public function isAfter(EqualsWithDateTimeInterface|DateTimeImmutable $other): bool
    {
        $otherDate = $other instanceof EqualsWithDateTimeInterface ? $other->value() : $other;

        return $this->date > $otherDate;
    }

    public function isBefore(EqualsWithDateTimeInterface|DateTimeImmutable $other): bool
    {
        $otherDate = $other instanceof EqualsWithDateTimeInterface ? $other->value() : $other;

        return $this->date < $otherDate;
    }
}
