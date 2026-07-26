<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Entity;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ErrorMessage;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\EmailSender\Domain\ValueObject\OutboxEmail\LockedAt;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Payload;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ScheduledAt;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\Shared\Domain\ValueObject\Tracing\TraceId;
use DateTimeImmutable;
use Symfony\Component\Clock\ClockInterface;

class OutboxEmail
{
    public function __construct(
        private readonly ?Id $id,
        private Status $status,
        private readonly Driver $driver,
        private readonly From $from,
        private readonly FromName $fromName,
        private readonly To $to,
        private readonly Subject $subject,
        private readonly Body $body,
        private readonly ?Payload $payload,
        private Attempts $attempts,
        private readonly ?TraceId $traceId,
        private ?ScheduledAt $scheduledAt = null,
        private ?LockedAt $lockedAt = null,
        private ?ErrorMessage $errorMessage = null,
    ) {
    }

    public static function create(
        Driver $driver,
        From $from,
        FromName $fromName,
        To $to,
        Subject $subject,
        Body $body,
        ?Payload $payload,
        TraceId $traceId,
    ): self {
        return new self(
            id: null,
            status: Status::created(),
            driver: $driver,
            from: $from,
            fromName: $fromName,
            to: $to,
            subject: $subject,
            body: $body,
            payload: $payload,
            attempts: Attempts::initialize(),
            traceId: $traceId,
            scheduledAt: null,
            lockedAt: null,
            errorMessage: null
        );
    }

    /**
     * @throws OutboxEmailAlreadyInProcessException
     */
    public function lock(DateTimeImmutable $now): void
    {
        if ($this->status->isProcessing() && null !== $this->lockedAt) {
            throw new OutboxEmailAlreadyInProcessException('Email is already being processed');
        }

        $this->status = Status::processing();
        $this->lockedAt = LockedAt::fromDateTime($now);
    }

    public function markAsSent(): void
    {
        $this->status = Status::sent();
        $this->scheduledAt = null;
        $this->lockedAt = null;
        $this->errorMessage = null;
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     */
    public function markAsFailed(string $error, DateTimeImmutable $nextAttemptAt): void
    {
        $this->status = Status::failed();
        $this->attempts = $this->attempts->increment();
        $this->errorMessage = ErrorMessage::fromString($error);
        $this->scheduledAt = ScheduledAt::fromDateTime($nextAttemptAt);
        $this->lockedAt = null;
    }

    /**
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailErrorMessageException
     */
    public function markAsFailedPermanently(string $error): void
    {
        $this->status = Status::failedPermanently();
        $this->errorMessage = ErrorMessage::fromString($error);
        $this->attempts = $this->attempts->increment();
        $this->scheduledAt = null;
        $this->lockedAt = null;
    }

    public function canBeProcessed(ClockInterface $clock): bool
    {
        return ($this->status->isCreated() || $this->status->isFailed())
            && (null === $this->scheduledAt || $this->scheduledAt->isInPast($clock));
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getFrom(): From
    {
        return $this->from;
    }

    public function getFromName(): FromName
    {
        return $this->fromName;
    }

    public function getTo(): To
    {
        return $this->to;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

    public function getPayload(): ?Payload
    {
        return $this->payload;
    }

    public function getAttempts(): Attempts
    {
        return $this->attempts;
    }

    public function getScheduledAt(): ?ScheduledAt
    {
        return $this->scheduledAt;
    }

    public function getLockedAt(): ?LockedAt
    {
        return $this->lockedAt;
    }

    public function getErrorMessage(): ?ErrorMessage
    {
        return $this->errorMessage;
    }

    public function getTraceId(): ?TraceId
    {
        return $this->traceId;
    }
}
