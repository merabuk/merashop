<?php

declare(strict_types=1);

namespace App\Tests\Resource\Fixture\EmailSender;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Infrastructure\Service\OutboxEmailFactory;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\TraceId;

final readonly class OutboxEmailMother
{
    public function __construct(
        private OutboxEmailFactory $outboxEmailFactory,
        private TraceIdFactoryInterface $traceIdFactory,
    ) {
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
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
     * @throws InvalidEmailSenderValueObjectException
     */
    public function createLockedEmail(): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $email->lock(new \DateTimeImmutable());

        return $email;
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
     */
    public function createFailedEmail(): OutboxEmail
    {
        $email = $this->createBaseEmail();
        $minutes = $email->getAttempts()->value() ** 2;
        $email->markAsFailed(
            error: 'Connection timeout',
            nextAttemptAt: new \DateTimeImmutable("+{$minutes} minutes")
        );

        return $email;
    }
}
