<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\Service;

use App\EmailSender\Domain\Service\OutboxRetryPolicy;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\Tests\Shared\BaseUnitTest;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;

final class OutboxRetryPolicyTest extends BaseUnitTest
{
    public function testItShouldRetryWorksCorrectly(): void
    {
        $maxAttempts = 3;
        $service = new OutboxRetryPolicy($maxAttempts);

        self::assertTrue($service->shouldRetry(Attempts::fromInt(0)));
        self::assertTrue($service->shouldRetry(Attempts::fromInt(1)));
        self::assertFalse($service->shouldRetry(Attempts::fromInt(2)));
    }

    #[DataProvider('nextAttemptAtDataProvider')]
    public function testItCalculatesNextAttemptAtCorrectly(int $currentAttempts, int $expectedDelayMinutes): void
    {
        $fixedNow = new DateTimeImmutable('2024-01-01 10:00:00');
        $service = new OutboxRetryPolicy(5);

        $nextAttempt = $service->calculateNextAttemptAt(
            attempts: Attempts::fromInt($currentAttempts),
            now: $fixedNow
        );

        $expectedDate = $fixedNow->modify("+{$expectedDelayMinutes} minutes");

        self::assertSame(
            $expectedDate->getTimestamp(),
            $nextAttempt->getTimestamp(),
            sprintf('Delay for %d attempts should be %d minutes', $currentAttempts, $expectedDelayMinutes)
        );
    }

    public static function nextAttemptAtDataProvider(): iterable
    {
        // (attempts + 1)^2
        yield 'after 0 failures (1st retry)' => [0, 1];
        yield 'after 1 failure (2nd retry)' => [1, 4];
        yield 'after 2 failures (3rd retry)' => [2, 9];
        yield 'after 3 failures (4th retry)' => [3, 16];
        yield 'after 4 failures (5th retry)' => [4, 25];
    }

    public function testGetMaxAttempts(): void
    {
        $maxAttempts = 10;
        $service = new OutboxRetryPolicy($maxAttempts);

        self::assertSame($maxAttempts, $service->getMaxAttempts());
    }
}
