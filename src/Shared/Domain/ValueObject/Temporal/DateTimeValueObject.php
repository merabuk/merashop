<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Temporal;

use DateTimeImmutable;

abstract readonly class DateTimeValueObject extends AbstractTemporalValueObject
{
    public const string COMPARISON_FORMAT = 'Y-m-d H:i:s.u';
    public const string INPUT_FORMAT = DateTimeImmutable::ATOM;
    public const string OUTPUT_FORMAT = DateTimeImmutable::ATOM;
}
