<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Shared\Domain\ValueObject\Contract\EqualsWithDateTimeInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;

trait AbstractTemporalValueObjectTrait
{
    use ValueObjectEqualityCheckTrait;

    protected function assertValidTemporalValue(
        string $className,
        string $toStringFormat,
        ?DateTimeImmutable $dateTime = null,
        bool $isDate = false,
    ): void {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $dateTime ??= new DateTimeImmutable();
        $dateTime = $isDate ? $dateTime->setTime(hour: 0, minute: 0, second: 0, microsecond: 0) : $dateTime;
        $vo = $className::fromDateTime($dateTime);

        $isDate
            ? Assert::assertEquals($dateTime, $vo->value())
            : Assert::assertSame($dateTime, $vo->value());
        Assert::assertSame($dateTime->format($toStringFormat), (string) $vo);
    }

    protected function assertValidTemporalValueFromString(
        string $className,
        string $toStringFormat,
        ?string $dateTimeString = null,
        bool $isDate = false,
    ): void {
        $this->assertHasStaticMethod($className, 'fromString');

        $dateTimeString ??= '1989-11-10 09:30:00.123456';
        $dateTimeObject = new DateTimeImmutable($dateTimeString);
        $dateTimeObject = $isDate ? $dateTimeObject->setTime(hour: 0, minute: 0, second: 0, microsecond: 0) : $dateTimeObject;
        $vo = $className::fromString($dateTimeString);

        Assert::assertEquals($dateTimeObject, $vo->value());
        Assert::assertSame($dateTimeObject->format($toStringFormat), (string) $vo);
    }

    protected function assertTemporalValueEqualityWithDateTime(
        string $className,
        string|DateTimeImmutable $value,
        string|DateTimeImmutable $anotherValue,
    ): void {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $date1 = is_string($value) ? new DateTimeImmutable($value) : $value;
        $date2 = is_string($anotherValue) ? new DateTimeImmutable($anotherValue) : $anotherValue;

        $vo = $className::fromDateTime($date1);

        $this->assertVoProvidesEqualityCheckWithDateTime($vo);

        Assert::assertTrue($vo->equalsWithDateTime($date1));
        Assert::assertFalse($vo->equalsWithDateTime($date2));
    }

    protected function assertTemporalValueIsAfter(
        string $className,
        string|DateTimeImmutable $earlierValue,
        string|DateTimeImmutable $laterValue,
    ): void {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $earlierDate = is_string($earlierValue) ? new DateTimeImmutable($earlierValue) : $earlierValue;
        $laterDate = is_string($laterValue) ? new DateTimeImmutable($laterValue) : $laterValue;

        $earlier = $className::fromDateTime($earlierDate);
        $later = $className::fromDateTime($laterDate);

        $this->assertVoProvidesEqualityCheckWithDateTime($earlier);

        Assert::assertTrue($later->isAfter($earlier), sprintf('%s must be after %s', $laterDate->format('Y-m-d H:i:s.u'), $earlierDate->format('Y-m-d H:i:s.u')));
        Assert::assertFalse($earlier->isAfter($later), sprintf('%s must NOT be after %s', $earlierDate->format('Y-m-d H:i:s.u'), $laterDate->format('Y-m-d H:i:s.u')));
        Assert::assertFalse($earlier->isAfter($earlier), 'Same date must NOT be after itself');
    }

    protected function assertTemporalValueIsBefore(
        string $className,
        string|DateTimeImmutable $earlierValue,
        string|DateTimeImmutable $laterValue,
    ): void {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $earlierDate = is_string($earlierValue) ? new DateTimeImmutable($earlierValue) : $earlierValue;
        $laterDate = is_string($laterValue) ? new DateTimeImmutable($laterValue) : $laterValue;

        $earlier = $className::fromDateTime($earlierDate);
        $later = $className::fromDateTime($laterDate);

        $this->assertVoProvidesEqualityCheckWithDateTime($earlier);

        Assert::assertTrue($earlier->isBefore($later), sprintf('%s must be before %s', $earlierDate->format('Y-m-d H:i:s.u'), $laterDate->format('Y-m-d H:i:s.u')));
        Assert::assertFalse($later->isBefore($earlier), sprintf('%s must NOT be before %s', $laterDate->format('Y-m-d H:i:s.u'), $earlierDate->format('Y-m-d H:i:s.u')));
        Assert::assertFalse($earlier->isBefore($earlier), 'Same date must NOT be before itself');
    }

    protected function assertVoProvidesEqualityCheckWithDateTime(object $vo): void
    {
        Assert::assertInstanceOf(EqualsWithDateTimeInterface::class, $vo);
    }
}
