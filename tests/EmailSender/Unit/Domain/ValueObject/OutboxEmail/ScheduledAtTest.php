<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\ValueObject\OutboxEmail\ScheduledAt;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateTimeValueObjectTrait;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ScheduledAtTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidLockedAt(): void
    {
        $this->assertCreatesValidDateTime(
            className: ScheduledAt::class,
            dateTime: new DateTimeImmutable('2024-01-01 12:00:00'),
        );
    }

    public function testItCanBeCreatedForNow(): void
    {
        $before = new DateTimeImmutable();
        $vo = ScheduledAt::now();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $vo->value());
        self::assertLessThanOrEqual($after, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(ScheduledAt::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEquality(ScheduledAt::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(ScheduledAt::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(ScheduledAt::class);
    }

    public function testItIsInPast(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $newClock = new MockClock('2024-01-01 11:00:00');
        $vo = ScheduledAt::now($clock);

        self::assertTrue($vo->isInPast($newClock));
    }
}
