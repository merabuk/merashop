<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use DateTimeImmutable;
use PHPUnit\Framework\Assert;

trait DateTimeValueObjectTrait
{
    protected function assertCreatesValidDateTime(
        string $className,
        ?DateTimeImmutable $dateTime = null,
        string $toStringFormat = DateTimeImmutable::ATOM
    ): void {
        $this->assertVoHasStaticMethod($className);

        $dateTime ??= new DateTimeImmutable();
        $vo = $className::fromDateTime($dateTime);

        Assert::assertSame($dateTime, $vo->value());
        Assert::assertSame($dateTime->format($toStringFormat), (string) $vo);
    }

    protected function assertDateTimeEquality(string $className): void
    {
        $this->assertVoHasStaticMethod($className);

        $dateString = '2024-01-01 15:30:00.123456';
        $diffString = '2024-01-01 15:30:00.123457';

        $vo1 = $className::fromDateTime(new DateTimeImmutable($dateString));
        $vo2 = $className::fromDateTime(new DateTimeImmutable($dateString));
        $vo3 = $className::fromDateTime(new DateTimeImmutable($diffString));

        Assert::assertTrue($vo1->equals($vo2), 'Same dates must be equal');
        Assert::assertTrue($vo1->equals($vo1), 'Date must be equal to itself');
        Assert::assertFalse($vo1->equals($vo3), 'Different dates must not be equal');
    }

    protected function assertDateTimeEqualityWithDateTime(string $className): void
    {
        $this->assertVoHasStaticMethod($className);

        $date1 = new DateTimeImmutable('2024-01-01 15:30:00.123456');
        $date2 = new DateTimeImmutable('2024-01-01 15:30:00.123457');

        $vo1 = $className::fromDateTime($date1);

        Assert::assertTrue($vo1->equalsWithDateTime($date1));
        Assert::assertFalse($vo1->equalsWithDateTime($date2));
    }

    protected function assertIsAfter(string $className): void
    {
        $this->assertVoHasStaticMethod($className);

        $earlier = $className::fromDateTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $later = $className::fromDateTime(new DateTimeImmutable('2024-01-01 11:00:00'));

        Assert::assertTrue($later->isAfter($earlier), '11:00 must be after 10:00');
        Assert::assertFalse($earlier->isAfter($later), '10:00 must NOT be after 11:00');
        Assert::assertFalse($earlier->isAfter($earlier), 'Same date must NOT be after itself');
    }

    protected function assertIsBefore(string $className): void
    {
        $this->assertVoHasStaticMethod($className);

        $earlier = $className::fromDateTime(new DateTimeImmutable('2024-01-01 10:00:00'));
        $later = $className::fromDateTime(new DateTimeImmutable('2024-01-01 11:00:00'));

        Assert::assertTrue($earlier->isBefore($later), '10:00 must be before 11:00');
        Assert::assertFalse($later->isBefore($earlier), '11:00 must NOT be before 10:00');
        Assert::assertFalse($earlier->isBefore($earlier), 'Same date must NOT be before itself');
    }

    private function assertVoHasStaticMethod(string $className): void
    {
        Assert::assertTrue(
            method_exists($className, 'fromDateTime'),
            sprintf('Class %s must implement static method fromDateTime', $className)
        );
    }
}
