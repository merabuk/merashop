<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
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
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\ValueObject\Tracing\TraceId;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<OutboxEmail, OrmOutboxEmail>
 */
final readonly class OutboxEmailMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmOutboxEmail
    {
        $this->assertIsType(OutboxEmail::class, $domain);
        /** @var OutboxEmail $domain */
        $orm = new OrmOutboxEmail();

        $orm->status = $domain->getStatus()->value();
        $orm->driver = $domain->getDriver()->value();
        $orm->from = $domain->getFrom()->value();
        $orm->fromName = $domain->getFromName()->value();
        $orm->to = $domain->getTo()->value();
        $orm->subject = $domain->getSubject()->value();
        $orm->body = $domain->getBody()->value();
        $orm->payload = $domain->getPayload()?->value();
        $orm->attempts = $domain->getAttempts()->value();
        $orm->traceId = $domain->getTraceId()?->value();
        $orm->scheduledAt = $domain->getScheduledAt()?->value();
        $orm->lockedAt = $domain->getLockedAt()?->value();
        $orm->errorMessage = $domain->getErrorMessage()?->value();

        return $orm;
    }

    /**
     * @throws EntityFieldMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    public function fromDoctrineOrm(object $orm): OutboxEmail
    {
        $this->assertIsType(OrmOutboxEmail::class, $orm);

        /* @var OrmOutboxEmail $orm */

        return new OutboxEmail(
            id: Id::fromInt($orm->id ?? throw EntityFieldMissingException::forEntityId(className: $orm::class)),
            status: Status::fromEnum($orm->status),
            driver: Driver::fromEnum($orm->driver),
            from: From::fromString($orm->from ?? throw EntityFieldMissingException::forField(field: 'from', className: $orm::class)),
            fromName: FromName::fromString($orm->fromName ?? throw EntityFieldMissingException::forField(field: 'fromName', className: $orm::class)),
            to: To::fromString($orm->to ?? throw EntityFieldMissingException::forField(field: 'to', className: $orm::class)),
            subject: Subject::fromString($orm->subject ?? throw EntityFieldMissingException::forField(field: 'subject', className: $orm::class)),
            body: Body::fromString($orm->body ?? throw EntityFieldMissingException::forField(field: 'body', className: $orm::class)),
            payload: null !== $orm->payload ? Payload::fromArray($orm->payload) : null,
            attempts: Attempts::fromInt($orm->attempts),
            traceId: null !== $orm->traceId ? TraceId::fromString($orm->traceId) : null,
            scheduledAt: null !== $orm->scheduledAt ? ScheduledAt::fromDateTime($orm->scheduledAt) : null,
            lockedAt: null !== $orm->lockedAt ? LockedAt::fromDateTime($orm->lockedAt) : null,
            errorMessage: null !== $orm->errorMessage ? ErrorMessage::fromString($orm->errorMessage) : null,
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(OutboxEmail::class, $domain);
        $this->assertIsType(OrmOutboxEmail::class, $orm);

        /* @var OutboxEmail $domain */
        /* @var OrmOutboxEmail $orm */

        $orm->status = $domain->getStatus()->value();
        $orm->attempts = $domain->getAttempts()->value();
        $orm->scheduledAt = $domain->getScheduledAt()?->value();
        $orm->lockedAt = $domain->getLockedAt()?->value();
        $orm->errorMessage = $domain->getErrorMessage()?->value();
    }
}
