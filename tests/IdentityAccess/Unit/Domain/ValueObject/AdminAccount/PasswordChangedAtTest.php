<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordChangedAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PasswordChangedAtTest extends TestCase
{
    public function testItCreatesValidPasswordChangedAt(): void
    {
        $date = new DateTimeImmutable('2024-01-01 12:00:00');
        $vo = PasswordChangedAt::fromDateTime($date);

        self::assertSame($date, $vo->value());
        self::assertSame($date->format(DateTimeImmutable::ATOM), (string) $vo);
    }

    public function testItCanBeCreatedForNow(): void
    {
        $before = new DateTimeImmutable();
        $vo = PasswordChangedAt::now();
        $after = new DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $vo->value());
        self::assertLessThanOrEqual($after, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $dateString1 = '2024-01-01 15:30:00.123456';
        $dateString2 = '2024-01-01 15:30:00.123457';

        $vo1 = PasswordChangedAt::fromDateTime(new DateTimeImmutable($dateString1));
        $vo2 = PasswordChangedAt::fromDateTime(new DateTimeImmutable($dateString1));
        $vo3 = PasswordChangedAt::fromDateTime(new DateTimeImmutable($dateString2));

        self::assertTrue($vo1->equals($vo2), 'Identical dates must be equal');
        self::assertFalse($vo1->equals($vo3), 'Dates with different microseconds must not be equal');
    }
}
