<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use PHPUnit\Framework\TestCase;

class AttemptsTest extends TestCase
{
    public function testItCreatesValidAttempts(): void
    {
        $attempts = 3;
        $vo = Attempts::fromInt($attempts);

        self::assertSame($attempts, $vo->value());
        self::assertSame((string) $attempts, (string) $vo);
    }

    public function testProvidesEqualityCheck(): void
    {
        $vo1 = Attempts::fromInt(1);
        $vo2 = Attempts::fromInt(1);
        $vo3 = Attempts::fromInt(2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testThrowsExceptionOnInvalidInput(): void
    {
        $this->expectException(InvalidOutboxEmailAttemptsException::class);
        Attempts::fromInt(-1);
    }

    public function testItInitializesCorrectly(): void
    {
        $vo = Attempts::initialize();

        self::assertSame(Attempts::DEFAULT, $vo->value());
    }

    public function testItIncrementsCorrectly(): void
    {
        $vo = Attempts::initialize();
        $newVo = $vo->increment();

        self::assertFalse($vo->equals($newVo));
        self::assertSame(Attempts::DEFAULT, $vo->value());
        self::assertSame(Attempts::DEFAULT + 1, $newVo->value());
    }

    public function testItChecksMaxAttempts(): void
    {
        $max = 5;
        $vo1 = Attempts::fromInt($max - 1);
        $vo2 = Attempts::fromInt($max);
        $vo3 = Attempts::fromInt($max + 1);

        self::assertFalse($vo1->hasExceeded($max));
        self::assertTrue($vo2->hasExceeded($max));
        self::assertTrue($vo3->hasExceeded($max));
    }
}
