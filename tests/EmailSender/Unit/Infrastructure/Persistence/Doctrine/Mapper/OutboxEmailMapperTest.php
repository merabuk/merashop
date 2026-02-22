<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Payload;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper\OutboxEmailMapper;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\ValueObject\TraceId;
use PHPUnit\Framework\TestCase;
use stdClass;

final class OutboxEmailMapperTest extends TestCase
{
    private OutboxEmailMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new OutboxEmailMapper();
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    public function testToDoctrineOrm(): void
    {
        $domain = $this->makeDomainEntity();

        $orm = $this->mapper->toDoctrineOrm($domain);

        self::assertNull($orm->id);
        self::assertEquals($domain->getStatus()->value(), $orm->status);
        self::assertEquals($domain->getDriver()->value(), $orm->driver);
        self::assertEquals($domain->getFrom()->value(), $orm->from);
        self::assertEquals($domain->getFromName()->value(), $orm->fromName);
        self::assertEquals($domain->getTo()->value(), $orm->to);
        self::assertEquals($domain->getSubject()->value(), $orm->subject);
        self::assertEquals($domain->getBody()->value(), $orm->body);
        self::assertEquals($domain->getPayload()?->value(), $orm->payload);
        self::assertEquals($domain->getAttempts()->value(), $orm->attempts);
        self::assertEquals($domain->getTraceId()->value(), $orm->traceId);
        self::assertEquals($domain->getScheduledAt()?->value(), $orm->scheduledAt);
        self::assertEquals($domain->getLockedAt()?->value(), $orm->lockedAt);
        self::assertEquals($domain->getErrorMessage()?->value(), $orm->errorMessage);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    public function testFromDoctrineOrm(): void
    {
        $orm = $this->makeOrmEntity();

        $domain = $this->mapper->fromDoctrineOrm($orm);

        self::assertEquals($orm->id, $domain->getId()->value());
        self::assertEquals($orm->status, $domain->getStatus()->value());
        self::assertEquals($orm->driver, $domain->getDriver()->value());
        self::assertEquals($orm->from, $domain->getFrom()->value());
        self::assertEquals($orm->fromName, $domain->getFromName()->value());
        self::assertEquals($orm->to, $domain->getTo()->value());
        self::assertEquals($orm->subject, $domain->getSubject()->value());
        self::assertEquals($orm->body, $domain->getBody()->value());
        self::assertEquals($orm->payload, $domain->getPayload()?->value());
        self::assertEquals($orm->attempts, $domain->getAttempts()->value());
        self::assertEquals($orm->traceId, $domain->getTraceId()->value());
        self::assertEquals($orm->scheduledAt, $domain->getScheduledAt()?->value());
        self::assertEquals($orm->lockedAt, $domain->getLockedAt()?->value());
        self::assertEquals($orm->errorMessage, $domain->getErrorMessage()?->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->makeDomainEntity();
        $orm = new OrmOutboxEmail();
        $orm->subject = 'Old Subject';

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertEquals($domain->getSubject()->value(), $orm->subject);
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws EntityIdMissingException
     * @throws InvalidTraceIdException
     */
    public function testThrowExceptionOnInvalidEntity(): void
    {
        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->fromDoctrineOrm(new stdClass());

        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->toDoctrineOrm(new stdClass());
    }

    /**
     * @throws InvalidTraceIdException
     * @throws InvalidEmailSenderValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmOutboxEmail());
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    private function makeDomainEntity(): OutboxEmail
    {
        $fakeId = 123;
        $status = StatusEnum::Created;
        $driver = DriverEnum::Log;
        $from = 'merashop@example.com';
        $fromName = 'MeraShop';
        $to = 'customer@example.com';
        $subject = 'Test Subject';
        $body = '<p>Test {{ $key }}</p>';
        $payload = ['key' => 'value'];
        $fakeTraceId = '01946393-271d-799d-8f2e-062e2467d018'; // UUID v7

        return new OutboxEmail(
            id: Id::fromInt($fakeId),
            status: Status::fromEnum($status),
            driver: Driver::fromEnum($driver),
            from: From::fromString($from),
            fromName: FromName::fromString($fromName),
            to: To::fromString($to),
            subject: Subject::fromString($subject),
            body: Body::fromString($body),
            payload: Payload::fromArray($payload),
            attempts: Attempts::initialize(),
            traceId: TraceId::fromString($fakeTraceId),
        );
    }

    private function makeOrmEntity(): OrmOutboxEmail
    {
        $fakeId = 123;
        $status = StatusEnum::Created;
        $driver = DriverEnum::Log;
        $from = 'merashop@example.com';
        $fromName = 'MeraShop';
        $to = 'customer@example.com';
        $subject = 'Test Subject';
        $body = '<p>Test {{ $key }}</p>';
        $payload = ['key' => 'value'];
        $fakeTraceId = '01946393-271d-799d-8f2e-062e2467d018'; // UUID v7

        $orm = new OrmOutboxEmail();
        $orm->setId($fakeId);
        $orm->status = $status;
        $orm->driver = $driver;
        $orm->from = $from;
        $orm->fromName = $fromName;
        $orm->to = $to;
        $orm->subject = $subject;
        $orm->body = $body;
        $orm->payload = $payload;
        $orm->traceId = $fakeTraceId;

        return $orm;
    }
}
