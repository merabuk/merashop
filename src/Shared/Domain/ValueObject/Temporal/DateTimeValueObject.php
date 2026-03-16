<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Temporal;

use App\Shared\Domain\ValueObject\Contract\EqualsWithDateTimeInterface;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use DateTimeImmutable;
use Stringable;

abstract readonly class DateTimeValueObject implements EqualsWithDateTimeInterface, EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const string COMPARISON_FORMAT = 'Y-m-d H:i:s.u';
    private const string OUTPUT_FORMAT = DateTimeImmutable::ATOM;

    private DateTimeImmutable $date;

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
        return $this->date->format(self::OUTPUT_FORMAT);
    }

    protected function getPrimitiveValue(): string
    {
        return $this->date->format(self::COMPARISON_FORMAT);
    }

    public function equalsWithDateTime(DateTimeImmutable $other): bool
    {
        return $this->date->format(self::COMPARISON_FORMAT) === $other->format(self::COMPARISON_FORMAT);
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
