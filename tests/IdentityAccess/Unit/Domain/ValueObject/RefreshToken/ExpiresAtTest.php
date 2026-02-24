<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ExpiresAtTest extends TestCase
{
    public function testItCreatesValidExpiresAt(): void
    {
        $date = new DateTimeImmutable('+1 hour');
        $expiresAt = ExpiresAt::fromDateTime($date);

        self::assertSame($date, $expiresAt->value());
        self::assertSame($date->format(DateTimeImmutable::ATOM), (string) $expiresAt);
    }

    public function testItChecksIfItIsExpired(): void
    {
        $expiresAt = ExpiresAt::fromDateTime(new DateTimeImmutable('-1 hour'));

        self::assertTrue($expiresAt->isExpired());
    }

    public function testItChecksIfItIsNotExpired(): void
    {
        $expiresAt = ExpiresAt::fromDateTime(new DateTimeImmutable('+1 hour'));

        self::assertFalse($expiresAt->isExpired());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $dateString1 = '2024-01-01 15:30:00.123456';
        $dateString2 = '2024-01-01 15:30:00.123457';

        $vo1 = ExpiresAt::fromDateTime(new DateTimeImmutable($dateString1));
        $vo2 = ExpiresAt::fromDateTime(new DateTimeImmutable($dateString1));
        $vo3 = ExpiresAt::fromDateTime(new DateTimeImmutable($dateString2));

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }
}
