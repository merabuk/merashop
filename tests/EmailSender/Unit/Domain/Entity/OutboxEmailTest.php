<?php

namespace App\Tests\EmailSender\Unit\Domain\Entity;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class OutboxEmailTest extends TestCase
{
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2024-01-01 10:00:00');
    }

    public function testSuccessfulLock(): void
    {
        $email = OutboxEmailMother::makeCreatedEmail();

        $email->lock($this->clock->now());

        self::assertTrue($email->getStatus()->isProcessing());
        self::assertNotNull($email->getLockedAt());
    }

    public function testCannotLockAlreadyProcessingEmail(): void
    {
        $email = OutboxEmailMother::makeLockedEmail();

        $this->expectException(OutboxEmailAlreadyInProcessException::class);
        $email->lock($this->clock->now());
    }

    public function testSuccessfulSentLockedEmail(): void
    {
        $email = OutboxEmailMother::makeLockedEmail();

        $email->markAsSent();

        self::assertTrue($email->getStatus()->isSent());
        self::assertNull($email->getScheduledAt());
        self::assertNull($email->getLockedAt());
        self::assertNull($email->getErrorMessage());
    }

    public function testMarkEmailAsFailed(): void
    {
        $email = OutboxEmailMother::makeLockedEmail();

        $error = 'Error message text';
        $date = $this->clock->now();

        $email->markAsFailed(
            error: $error,
            nextAttemptAt: $date,
        );

        self::assertTrue($email->getStatus()->isFailed());
        self::assertSame(1, $email->getAttempts()->value());
        self::assertSame($error, $email->getErrorMessage()->value());
        self::assertTrue($email->getScheduledAt()?->equalsWithDateTime($date));
        self::assertNull($email->getLockedAt());
    }

    public function testMarkEmailAsFailedPermanently(): void
    {
        $email = OutboxEmailMother::makeFailedEmail(attempts: 4);

        $error = 'Error message text';

        $email->markAsFailedPermanently(error: $error);

        self::assertTrue($email->getStatus()->isFailedPermanently());
        self::assertSame(5, $email->getAttempts()->value());
        self::assertSame($error, $email->getErrorMessage()->value());
        self::assertNull($email->getScheduledAt());
        self::assertNull($email->getLockedAt());
    }

    #[DataProvider('canBeProcessedProvider')]
    public function testEmailCanBeProcessed(OutboxEmail $outboxEmail, bool $expectedResult): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        self::assertSame($outboxEmail->canBeProcessed($clock), $expectedResult);
    }

    public static function canBeProcessedProvider(): iterable
    {
        yield 'created' => [OutboxEmailMother::makeCreatedEmail(), true];
        yield 'locked' => [OutboxEmailMother::makeLockedEmail(), false];
        yield 'failed' => [OutboxEmailMother::makeFailedEmail(
            scheduledAt: new DateTimeImmutable('2024-01-01 10:00:00')->modify('-1 seconds')
        ), true];
        yield 'sent' => [OutboxEmailMother::makeSentEmail(), false];
        yield 'failedPermanently' => [OutboxEmailMother::makeFailedPermanentlyEmail(), false];
    }
}
