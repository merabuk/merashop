<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\Shared\Domain\ValueObject\TraceId;
use DateTimeImmutable;

final readonly class OutboxEmailFixture
{
    public function __construct(
        private OutboxEmailMother $mother,
        private OutboxEmailWriteRepositoryInterface $repository,
    ) {
    }

    public function create(
        ?StatusEnum $status = null,
        ?DriverEnum $driver = null,
        ?string $from = null,
        ?string $fromName = null,
        ?string $to = null,
        ?string $subject = null,
        ?string $body = null,
        ?array $context = null,
        ?int $attempts = null,
        ?TraceId $traceId = null,
        ?DateTimeImmutable $scheduledAt = null,
        ?DateTimeImmutable $lockedAt = null,
        ?string $errorMessage = null,
    ): OutboxEmail {
        $email = $this->mother->createBaseEmail(
            status: $status,
            driver: $driver,
            from: $from,
            fromName: $fromName,
            to: $to,
            subject: $subject,
            body: $body,
            context: $context,
            attempts: $attempts,
            traceId: $traceId,
            scheduledAt: $scheduledAt,
            lockedAt: $lockedAt,
            errorMessage: $errorMessage
        );

        return $this->repository->save($email);
    }

    public function createCreatedEmail(): OutboxEmail
    {
        $email = $this->mother->createCreatedEmail();

        return $this->repository->save($email);
    }

    public function createSentEmail(): OutboxEmail
    {
        $email = $this->mother->createSentEmail();

        return $this->repository->save($email);
    }

    public function createLockedEmail(?DateTimeImmutable $lockedAt = null): OutboxEmail
    {
        $email = $this->mother->createLockedEmail($lockedAt);

        return $this->repository->save($email);
    }

    public function createFailedEmail(
        int $attempts = 1,
        ?string $errorMessage = null,
        ?DateTimeImmutable $nextAttemptAt = null,
    ): OutboxEmail {
        $email = $this->mother->createFailedEmail(
            attempts: $attempts,
            errorMessage: $errorMessage,
            nextAttemptAt: $nextAttemptAt
        );

        return $this->repository->save($email);
    }

    public function createFailedPermanentlyEmail(
        int $attempts = 5,
        ?string $errorMessage = null,
    ): OutboxEmail {
        $email = $this->mother->createFailedPermanentlyEmail(
            attempts: $attempts,
            errorMessage: $errorMessage
        );

        return $this->repository->save($email);
    }
}
