<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateTimeValueObjectTrait;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ExpiresAtTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidExpiresAt(): void
    {
        $this->assertCreatesValidDateTime(
            className: ExpiresAt::class,
            dateTime: new DateTimeImmutable('+1 hour'),
        );
    }

    public function testItChecksIfItIsExpired(): void
    {
        $clock = new MockClock();
        $expiresAt = ExpiresAt::fromDateTime(new DateTimeImmutable('-1 hour'));

        self::assertTrue($expiresAt->isExpired($clock));
    }

    public function testItChecksIfItIsNotExpired(): void
    {
        $clock = new MockClock();
        $expiresAt = ExpiresAt::fromDateTime(new DateTimeImmutable('+1 hour'));

        self::assertFalse($expiresAt->isExpired($clock));
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(ExpiresAt::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEqualityWithDateTime(ExpiresAt::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(ExpiresAt::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(ExpiresAt::class);
    }
}
