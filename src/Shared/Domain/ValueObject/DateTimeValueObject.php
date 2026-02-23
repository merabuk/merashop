<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use DateTimeImmutable;
use Stringable;

abstract readonly class DateTimeValueObject implements Stringable
{
    use ValueObjectEqualityTrait;

    private const string COMPARISON_FORMAT = 'Y-m-d H:i:s.u';
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
}
