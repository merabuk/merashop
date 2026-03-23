<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Temporal;

use DateTimeImmutable;

abstract readonly class DateValueObject extends AbstractTemporalValueObject
{
    public const string COMPARISON_FORMAT = 'Y-m-d';
    public const string INPUT_FORMAT = 'Y-m-d';
    public const string OUTPUT_FORMAT = 'Y-m-d';

    protected function __construct(DateTimeImmutable $date)
    {
        parent::__construct($date->setTime(hour: 0, minute: 0, second: 0, microsecond: 0));
    }
}
