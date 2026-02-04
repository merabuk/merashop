<?php

namespace App\Tests\EmailSender\Unit\Domain\Entity\Entity;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Clock\MockClock;

class OutboxEmailTest extends TestCase
{
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2024-01-01 10:00:00');
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testSuccessfulLock(): void
    {
        $email = $this->createBaseEmail();

        $email->lock($this->clock->now());

        self::assertTrue($email->getStatus()->isProcessing());
        self::assertNotNull($email->getLockedAt());
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testCannotLockAlreadyProcessingEmail(): void
    {
        $email = $this->createLockedEmail();

        $this->expectException(OutboxEmailAlreadyInProcessException::class);
        $email->lock($this->clock->now());
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testSuccessfulSentLockedEmail(): void
    {
        $email = $this->createLockedEmail();

        $email->markAsSent();

        self::assertTrue($email->getStatus()->isSent());
        self::assertNull($email->getScheduledAt());
        self::assertNull($email->getLockedAt());
        self::assertNull($email->getErrorMessage());
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testMarkEmailAsFailed(): void
    {
        $email = $this->createLockedEmail();

        $error = 'Error message text';
        $date = $this->clock->now();
        $format = $date::ATOM;

        $email->markAsFailed(
            error: $error,
            nextAttemptAt: $date,
        );

        self::assertTrue($email->getStatus()->isFailed());
        self::assertEquals(1, $email->getAttempts()->value());
        self::assertEquals($error, $email->getErrorMessage()->value());
        self::assertEquals(
            expected: $date->format($format),
            actual: $email->getScheduledAt()?->value()->format($format),
        );
        self::assertNull($email->getLockedAt());
    }

    /**
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testMarkEmailAsFailedPermanently(): void
    {
        $email = $this->createLockedEmail();

        $error = 'Error message text';

        $email->markAsFailedPermanently(error: $error);

        self::assertTrue($email->getStatus()->isFailedPermanently());
        self::assertEquals(0, $email->getAttempts()->value());
        self::assertEquals($error, $email->getErrorMessage()->value());
        self::assertNull($email->getScheduledAt());
        self::assertNull($email->getLockedAt());
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function testEmailCanBeProcessed(): void
    {
        $createdEmail = $this->createBaseEmail();
        $lockedEmail = $this->createLockedEmail();
        $failedEmail = $this->createFailedEmail();
        $sentEmail = $this->createSentEmail();
        $failedPermanently = $this->createFailedPermanentlyEmail();

        self::assertTrue($createdEmail->canBeProcessed());
        self::assertFalse($lockedEmail->canBeProcessed());
        self::assertTrue($failedEmail->canBeProcessed());
        self::assertFalse($sentEmail->canBeProcessed());
        self::assertFalse($failedPermanently->canBeProcessed());
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    private function createSentEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsSent();

        return $email;
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws OutboxEmailAlreadyInProcessException
     */
    private function createFailedEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsFailed(
            error: 'Error message text',
            nextAttemptAt: $this->clock->now(),
        );

        return $email;
    }

    /**
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws OutboxEmailAlreadyInProcessException
     */
    private function createFailedPermanentlyEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsFailedPermanently(error: 'Error message text');

        return $email;
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    private function createLockedEmail(): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $email->lock($this->clock->now());

        return $email;
    }

    private function createBaseEmail(int $attempts = 0): OutboxEmail
    {
        try {
            return new OutboxEmail(
                id: null,
                status: Status::fromEnum(StatusEnum::Created),
                driver: Driver::fromEnum(DriverEnum::Log),
                from: From::fromString('no-reply.merashop@example.com'),
                fromName: FromName::fromString('MeraShop'),
                to: To::fromString('test@example.com'),
                subject: Subject::fromString('Test subject'),
                body: Body::fromString('Test body'),
                payload: null,
                attempts: Attempts::fromInt($attempts),
                traceId: null,
                scheduledAt: null,
                lockedAt: null,
                errorMessage: null,
            );
        } catch (InvalidEmailSenderValueObjectException $e) {
            throw new RuntimeException(sprintf('Invalid email sender value object. Error: %s', $e->getMessage()));
        }
    }
}
