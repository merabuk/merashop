<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Support;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Exception\OutboxEmailAlreadyInProcessException;
use App\EmailSender\Domain\Service\OutboxEmailFactoryInterface;
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
