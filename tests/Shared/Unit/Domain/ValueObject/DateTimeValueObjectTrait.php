<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Shared\Domain\ValueObject\EqualsWithDateTimeInterface;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;

trait DateTimeValueObjectTrait
{
    use ValueObjectEqualityCheckTrait;

    protected function assertCreatesValidDateTime(
        string $className,
        ?DateTimeImmutable $dateTime = null,
        string $toStringFormat = DateTimeImmutable::ATOM,
    ): void {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $dateTime ??= new DateTimeImmutable();
        $vo = $className::fromDateTime($dateTime);

        Assert::assertSame($dateTime, $vo->value());
        Assert::assertSame($dateTime->format($toStringFormat), (string) $vo);
    }

    protected function assertDateTimeEquality(string $className): void
    {
        $this->assertDateTimeVOProvidesEqualityCheck(
            className: $className,
            value: '2024-01-01 15:30:00.123456',
            anotherValue: '2024-01-01 15:30:00.123457',
        );
    }

    protected function assertDateTimeEqualityWithDateTime(string $className): void
    {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $date1 = new DateTimeImmutable('2024-01-01 15:30:00.123456');
        $date2 = new DateTimeImmutable('2024-01-01 15:30:00.123457');

        $vo = $className::fromDateTime($date1);

        $this->assertVoProvidesEqualityCheckWithDateTime($vo);

        Assert::assertTrue($vo->equalsWithDateTime($date1));
        Assert::assertFalse($vo->equalsWithDateTime($date2));
    }

    protected function assertIsAfter(string $className): void
    {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $earlier = $className::fromDateTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $later = $className::fromDateTime(new DateTimeImmutable('2024-01-01 11:00:00'));

        $this->assertVoProvidesEqualityCheckWithDateTime($earlier);

        Assert::assertTrue($later->isAfter($earlier), '11:00 must be after 10:00');
        Assert::assertFalse($earlier->isAfter($later), '10:00 must NOT be after 11:00');
        Assert::assertFalse($earlier->isAfter($earlier), 'Same date must NOT be after itself');
    }

    protected function assertIsBefore(string $className): void
    {
        $this->assertHasStaticMethod($className, 'fromDateTime');

        $earlier = $className::fromDateTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $later = $className::fromDateTime(new DateTimeImmutable('2024-01-01 11:00:00'));

        $this->assertVoProvidesEqualityCheckWithDateTime($earlier);

        Assert::assertTrue($earlier->isBefore($later), '10:00 must be before 11:00');
        Assert::assertFalse($later->isBefore($earlier), '11:00 must NOT be before 10:00');
        Assert::assertFalse($earlier->isBefore($earlier), 'Same date must NOT be before itself');
    }

    private function assertVoProvidesEqualityCheckWithDateTime(object $vo): void
    {
        Assert::assertInstanceOf(EqualsWithDateTimeInterface::class, $vo);
    }
}
