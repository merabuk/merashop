<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordChangedAt;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateTimeValueObjectTrait;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PasswordChangedAtTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidPasswordChangedAt(): void
    {
        $this->assertCreatesValidDateTime(
            className: PasswordChangedAt::class,
            dateTime: new DateTimeImmutable('2024-01-01 12:00:00'),
        );
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
        $this->assertDateTimeEquality(PasswordChangedAt::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEqualityWithDateTime(PasswordChangedAt::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(PasswordChangedAt::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(PasswordChangedAt::class);
    }
}
