<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\EmailSender\Domain\Service\OutboxEmailFactoryInterface;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ErrorMessage;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\LockedAt;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Payload;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ScheduledAt;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\TraceId;
use DateMalformedStringException;
use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;

final readonly class OutboxEmailMother
{
    public function __construct(
        private OutboxEmailFactoryInterface $outboxEmailFactory,
        private TraceIdFactoryInterface $traceIdFactory,
        private ClockInterface $clock,
    ) {
    }

    public static function makeSentEmail(): OutboxEmail
    {
        $outboxEmail = self::createWithData();

        $outboxEmail->markAsSent();

        return $outboxEmail;
    }

    public static function makeLockedEmail(?DateTimeImmutable $now = null): OutboxEmail
    {
        $outboxEmail = self::createWithData();

        $outboxEmail->lock($now ?? new DateTimeImmutable());

        return $outboxEmail;
    }

    public static function makeFailedEmail(
        int $attempts = 1,
        ?string $errorMessage = null,
        ?DateTimeImmutable $nextAttemptAt = null,
    ): OutboxEmail {
        $outboxEmail = self::createWithData(attempts: $attempts);

        $outboxEmail->markAsFailed(
            error: $errorMessage ?? 'Connection timeout',
            nextAttemptAt: $nextAttemptAt ?? new DateTimeImmutable(sprintf('+%d minutes', $outboxEmail->getAttempts()->value()))
        );

        return $outboxEmail;
    }

    public static function makeFailedPermanentlyEmail(
        int $attempts = 5,
        ?string $errorMessage = null,
    ): OutboxEmail {
        $outboxEmail = self::createWithData(attempts: $attempts);

        $outboxEmail->markAsFailedPermanently(
            error: $errorMessage ?? 'Connection timeout',
        );

        return $outboxEmail;
    }

    public static function makeCreatedEmail(): OutboxEmail
    {
        return self::createWithData();
    }

    public static function createWithData(
        ?StatusEnum $status = null,
        ?DriverEnum $driver = null,
        ?string $from = null,
        ?string $fromName = null,
        ?string $to = null,
        ?string $subject = null,
        ?string $body = null,
        ?array $payload = null,
        ?int $attempts = 0,
        ?TraceId $traceId = null,
        ?DateTimeImmutable $scheduledAt = null,
        ?DateTimeImmutable $lockedAt = null,
        ?string $errorMessage = null,
    ): OutboxEmail {
        return new OutboxEmail(
            id: null,
            status: Status::fromEnum($status ?? StatusEnum::Created),
            driver: Driver::fromEnum($driver ?? DriverEnum::Log),
            from: From::fromString($from ?? 'no-reply.merashop@example.com'),
            fromName: FromName::fromString($fromName ?? 'MeraShop'),
            to: To::fromString($to ?? 'test@example.com'),
            subject: Subject::fromString($subject ?? 'Test subject'),
            body: Body::fromString($body ?? '<p>Test {{ $key }}</p>'),
            payload: Payload::fromArray($payload ?? ['key' => 'body']),
            attempts: Attempts::fromInt($attempts),
            traceId: $traceId,
            scheduledAt: $scheduledAt ? ScheduledAt::fromDateTime($scheduledAt) : null,
            lockedAt: $lockedAt ? LockedAt::fromDateTime($lockedAt) : null,
            errorMessage: $errorMessage ? ErrorMessage::fromString($errorMessage) : null,
        );
    }

    /**
     * @throws TraceIdFactoryException
     */
    public function createBaseEmail(
        string $to = 'test@example.com',
        string $subject = 'Subject',
        string $body = 'Body',
        array $context = [],
        ?TraceId $traceId = null,
    ): OutboxEmail {
        return $this->outboxEmailFactory->createForTest(
            driver: DriverEnum::Log,
            from: 'no-reply.merashop@example.com',
            fromName: 'MeraShop',
            to: $to,
            subject: $subject,
            body: $body,
            context: $context,
            traceId: $traceId ?? $this->traceIdFactory->createNew()
        );
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     * @throws TraceIdFactoryException
     */
    public function createLockedEmail(?DateTimeImmutable $lockedAt = null): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $email->lock($lockedAt ?? $this->clock->now());

        return $email;
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws DateMalformedStringException
     * @throws TraceIdFactoryException
     */
    public function createFailedEmail(): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $minutes = $email->getAttempts()->value() ** 2;
        $email->markAsFailed(
            error: 'Connection timeout',
            nextAttemptAt: $this->clock->now()->modify("+{$minutes} minutes")
        );

        return $email;
    }
}
