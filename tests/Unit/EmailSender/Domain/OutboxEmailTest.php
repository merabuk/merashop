<?php

namespace App\Tests\Unit\EmailSender\Domain;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
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

class OutboxEmailTest extends TestCase
{
    public function testSuccessfulLock(): void
    {
        $email = $this->createBaseEmail();

        $email->lock(new \DateTimeImmutable());

        $this->assertTrue($email->getStatus()->isProcessing());
        $this->assertNotNull($email->getLockedAt());
    }

    public function testCannotLockAlreadyProcessingEmail(): void
    {
        $email = $this->createLockedEmail();

        $this->expectException(OutboxEmailAlreadyInProcessException::class);
        $email->lock(new \DateTimeImmutable());
    }

    public function testSuccessfulSentLockedEmail(): void
    {
        $email = $this->createLockedEmail();

        $email->markAsSent();

        $this->assertTrue($email->getStatus()->isSent());
        $this->assertNull($email->getScheduledAt());
        $this->assertNull($email->getLockedAt());
        $this->assertNull($email->getErrorMessage());
    }

    public function testMarkEmailAsFailed(): void
    {
        $email = $this->createLockedEmail();

        $error = 'Error message text';
        $date = new \DateTimeImmutable();
        $format = \DateTimeImmutable::ATOM;

        $email->markAsFailed(
            error: $error,
            nextAttemptAt: $date,
        );

        $this->assertTrue($email->getStatus()->isFailed());
        $this->assertEquals(1, $email->getAttempts()->value());
        $this->assertEquals($error, $email->getErrorMessage()->value());
        $this->assertEquals(
            expected: $date->format($format),
            actual: $email->getScheduledAt()?->value()->format($format),
        );
        $this->assertNull($email->getLockedAt());
    }

    public function testMarkEmailAsFailedPermanently(): void
    {
        $email = $this->createLockedEmail();

        $error = 'Error message text';

        $email->markAsFailedPermanently(error: $error);

        $this->assertTrue($email->getStatus()->isFailedPermanently());
        $this->assertEquals(0, $email->getAttempts()->value());
        $this->assertEquals($error, $email->getErrorMessage()->value());
        $this->assertNull($email->getScheduledAt());
        $this->assertNull($email->getLockedAt());
    }

    public function testEmailCanBeProcessed(): void
    {
        $createdEmail = $this->createBaseEmail();
        $lockedEmail = $this->createLockedEmail();
        $failedEmail = $this->createFailedEmail();
        $sentEmail = $this->createSentEmail();
        $failedPermanently = $this->createFailedPermanentlyEmail();

        $this->assertTrue($createdEmail->canBeProcessed());
        $this->assertFalse($lockedEmail->canBeProcessed());
        $this->assertTrue($failedEmail->canBeProcessed());
        $this->assertFalse($sentEmail->canBeProcessed());
        $this->assertFalse($failedPermanently->canBeProcessed());
    }

    private function createSentEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsSent();

        return $email;
    }

    private function createFailedEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsFailed(
            error: 'Error message text',
            nextAttemptAt: new \DateTimeImmutable(),
        );

        return $email;
    }

    private function createFailedPermanentlyEmail(): OutboxEmail
    {
        $email = $this->createLockedEmail();
        $email->markAsFailedPermanently(error: 'Error message text');

        return $email;
    }

    private function createLockedEmail(): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $email->lock(new \DateTimeImmutable());

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
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
