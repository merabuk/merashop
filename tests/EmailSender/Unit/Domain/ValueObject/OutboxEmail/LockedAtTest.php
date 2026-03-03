<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\ValueObject\OutboxEmail\LockedAt;
use App\Tests\Shared\Unit\Domain\ValueObject\DateTimeValueObjectTrait;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LockedAtTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidLockedAt(): void
    {
        $this->assertCreatesValidDateTime(
            className: LockedAt::class,
            dateTime: new DateTimeImmutable('2024-01-01 12:00:00'),
        );
    }

    public function testItCanBeCreatedForNow(): void
    {
        $before = new DateTimeImmutable();
        $vo = LockedAt::now();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $vo->value());
        self::assertLessThanOrEqual($after, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(LockedAt::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEquality(LockedAt::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(LockedAt::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(LockedAt::class);
    }
}
