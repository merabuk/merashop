<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Shared\Domain\ValueObject\Temporal\DateValueObject;
use DateTimeImmutable;

trait DateValueObjectTrait
{
    use AbstractTemporalValueObjectTrait;

    protected function assertCreatesValidDateTime(
        string $className,
        ?DateTimeImmutable $dateTime = null,
        string $toStringFormat = DateValueObject::OUTPUT_FORMAT,
    ): void {
        $this->assertValidTemporalValue(
            className: $className,
            toStringFormat: $toStringFormat,
            dateTime: $dateTime,
            isDate: true
        );
    }

    protected function assertCreatesValidDateTimeFromString(
        string $className,
        ?string $dateTimeString = null,
        string $toStringFormat = DateValueObject::OUTPUT_FORMAT,
    ): void {
        $this->assertValidTemporalValueFromString(
            className: $className,
            toStringFormat: $toStringFormat,
            dateTimeString: $dateTimeString,
            isDate: true
        );
    }

    protected function assertDateTimeEquality(string $className): void
    {
        $this->assertDateTimeVOProvidesEqualityCheck(
            className: $className,
            value: '2024-01-01 15:30:00.123456',
            anotherValue: '2024-01-02 15:30:00.123456',
        );
    }

    protected function assertDateTimeEqualityWithDateTime(string $className): void
    {
        $this->assertTemporalValueEqualityWithDateTime(
            className: $className,
            value: '2024-01-01 15:30:00.123456',
            anotherValue: '2024-01-02 15:30:00.123456',
        );
    }

    protected function assertIsAfter(string $className): void
    {
        $this->assertTemporalValueIsAfter(
            className: $className,
            earlierValue: '2024-01-01 10:00:00',
            laterValue: '2024-01-02 10:00:00',
        );
    }

    protected function assertIsBefore(string $className): void
    {
        $this->assertTemporalValueIsBefore(
            className: $className,
            earlierValue: '2024-01-01 10:00:00',
            laterValue: '2024-01-02 10:00:00',
        );
    }
}
