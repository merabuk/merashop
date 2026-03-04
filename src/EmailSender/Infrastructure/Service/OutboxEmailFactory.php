<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailAttemptsException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailBodyException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailDriverException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailFromException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailFromNameException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailToException;
use App\EmailSender\Domain\Factory\Contracts\OutboxEmailFactoryInterface;
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
use App\Shared\Domain\ValueObject\TraceId;
use DateTimeImmutable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final readonly class OutboxEmailFactory implements OutboxEmailFactoryInterface
{
    public function __construct(
        private Environment $twig,
        private string $defaultFrom,
        private string $defaultFromName,
        private string $defaultMailDriver,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws InvalidOutboxEmailBodyException
     * @throws InvalidOutboxEmailDriverException
     * @throws InvalidOutboxEmailFromException
     * @throws InvalidOutboxEmailFromNameException
     * @throws InvalidOutboxEmailSubjectException
     * @throws InvalidOutboxEmailToException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createFromTemplate(
        string $to,
        string $subject,
        string $template,
        array $context,
        TraceId $traceId,
    ): OutboxEmail {
        return OutboxEmail::create(
            driver: Driver::fromString($this->defaultMailDriver),
            from: From::fromString($this->defaultFrom),
            fromName: FromName::fromString($this->defaultFromName),
            to: To::fromString($to),
            subject: Subject::fromString($subject),
            body: Body::fromString($this->twig->render($template, $context)),
            payload: Payload::fromArray($context),
            traceId: $traceId
        );
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws InvalidOutboxEmailAttemptsException
     * @throws InvalidOutboxEmailBodyException
     * @throws InvalidOutboxEmailErrorMessageException
     * @throws InvalidOutboxEmailFromException
     * @throws InvalidOutboxEmailFromNameException
     * @throws InvalidOutboxEmailToException
     * @throws InvalidOutboxEmailSubjectException
     */
    public function createForTest(
        StatusEnum $status,
        DriverEnum $driver,
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $body,
        array $context,
        TraceId $traceId,
        ?int $attempts = null,
        ?DateTimeImmutable $scheduledAt = null,
        ?DateTimeImmutable $lockedAt = null,
        ?string $errorMessage = null,
    ): OutboxEmail {
        return new OutboxEmail(
            id: null,
            status: Status::fromEnum($status),
            driver: Driver::fromEnum($driver),
            from: From::fromString($from),
            fromName: FromName::fromString($fromName),
            to: To::fromString($to),
            subject: Subject::fromString($subject),
            body: Body::fromString($body),
            payload: Payload::fromArray($context),
            attempts: $attempts ? Attempts::fromInt($attempts) : Attempts::initialize(),
            traceId: $traceId,
            scheduledAt: $scheduledAt ? ScheduledAt::fromDateTime($scheduledAt) : null,
            lockedAt: $lockedAt ? LockedAt::fromDateTime($lockedAt) : null,
            errorMessage: $errorMessage ? ErrorMessage::fromString($errorMessage) : null,
        );
    }
}
