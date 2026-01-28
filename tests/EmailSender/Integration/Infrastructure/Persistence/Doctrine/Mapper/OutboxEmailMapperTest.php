<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Integration\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectExceptionInterface;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper\OutboxEmailMapper;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OutboxEmailMapperTest extends KernelTestCase
{
    private OutboxEmailMapper $mapper;
    private OutboxEmailMother $mother;
    private TraceIdFactoryInterface $traceIdFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mapper = self::getContainer()->get(OutboxEmailMapper::class);
        $this->mother = self::getContainer()->get(OutboxEmailMother::class);
        $this->traceIdFactory = self::getContainer()->get(TraceIdFactoryInterface::class);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidEmailSenderValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     * @throws InvalidTraceIdException
     */
    public function testFullMappingCycle(): void
    {
        $fakeId = 123;
        $domain = $this->mother->createBaseEmail(traceId: $this->traceIdFactory->createNew());

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

        // emulating ID from the database
        $reflection = new \ReflectionProperty(OrmOutboxEmail::class, 'id');
        $reflection->setValue($orm, $fakeId);

        $restoredDomain = $this->mapper->fromDoctrineOrm($orm);
        self::assertEquals($fakeId, $restoredDomain->getId()->value());
        self::assertEquals($domain->getStatus()->value(), $restoredDomain->getStatus()->value());
        self::assertEquals($domain->getDriver()->value(), $restoredDomain->getDriver()->value());
        self::assertEquals($domain->getFrom()->value(), $restoredDomain->getFrom()->value());
        self::assertEquals($domain->getFromName()->value(), $restoredDomain->getFromName()->value());
        self::assertEquals($domain->getTo()->value(), $restoredDomain->getTo()->value());
        self::assertEquals($domain->getSubject()->value(), $restoredDomain->getSubject()->value());
        self::assertEquals($domain->getBody()->value(), $restoredDomain->getBody()->value());
        self::assertEquals($domain->getPayload()?->value(), $restoredDomain->getPayload()?->value());
        self::assertEquals($domain->getAttempts()->value(), $restoredDomain->getAttempts()->value());
        self::assertEquals($domain->getTraceId()->value(), $restoredDomain->getTraceId()->value());
        self::assertEquals($domain->getScheduledAt()?->value(), $restoredDomain->getScheduledAt()?->value());
        self::assertEquals($domain->getLockedAt()?->value(), $restoredDomain->getLockedAt()?->value());
        self::assertEquals($domain->getErrorMessage()?->value(), $restoredDomain->getErrorMessage()?->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->mother->createBaseEmail();
        $orm = new OrmOutboxEmail();
        $orm->subject = 'Old Subject';

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertEquals($domain->getSubject()->value(), $orm->subject);
    }

    /**
     * @throws InvalidEmailSenderValueObjectExceptionInterface
     * @throws EntityIdMissingException
     * @throws InvalidTraceIdException
     */
    public function testThrowExceptionOnInvalidEntity(): void
    {
        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->fromDoctrineOrm(new \stdClass());

        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->toDoctrineOrm(new \stdClass());
    }

    /**
     * @throws InvalidTraceIdException
     * @throws InvalidEmailSenderValueObjectExceptionInterface
     * @throws IncompatibleMappedEntityException
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmOutboxEmail());
    }
}
