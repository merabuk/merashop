<?php

declare(strict_types=1);

namespace App\Tests\Integration\EmailSender\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper\OutboxEmailMapper;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Tests\Resource\Fixture\EmailSender\OutboxEmailMother;
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
     * @throws InvalidEmailSenderValueObjectException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidTraceIdException
     */
    public function testFullMappingCycle(): void
    {
        $fakeId = 123;
        $domain = $this->mother->createBaseEmail(traceId: $this->traceIdFactory->createNew());

        $orm = $this->mapper->toDoctrineOrm($domain);

        $this->assertNull($orm->id);
        $this->assertEquals($domain->getStatus()->value(), $orm->status);
        $this->assertEquals($domain->getDriver()->value(), $orm->driver);
        $this->assertEquals($domain->getFrom()->value(), $orm->from);
        $this->assertEquals($domain->getFromName()->value(), $orm->fromName);
        $this->assertEquals($domain->getTo()->value(), $orm->to);
        $this->assertEquals($domain->getSubject()->value(), $orm->subject);
        $this->assertEquals($domain->getBody()->value(), $orm->body);
        $this->assertEquals($domain->getPayload()?->value(), $orm->payload);
        $this->assertEquals($domain->getAttempts()->value(), $orm->attempts);
        $this->assertEquals($domain->getTraceId()->value(), $orm->traceId);
        $this->assertEquals($domain->getScheduledAt()?->value(), $orm->scheduledAt);
        $this->assertEquals($domain->getLockedAt()?->value(), $orm->lockedAt);
        $this->assertEquals($domain->getErrorMessage()?->value(), $orm->errorMessage);

        // emulating ID from the database
        $reflection = new \ReflectionProperty(OrmOutboxEmail::class, 'id');
        $reflection->setValue($orm, $fakeId);

        $restoredDomain = $this->mapper->fromDoctrineOrm($orm);
        $this->assertEquals($fakeId, $restoredDomain->getId()->value());
        $this->assertEquals($domain->getStatus()->value(), $restoredDomain->getStatus()->value());
        $this->assertEquals($domain->getDriver()->value(), $restoredDomain->getDriver()->value());
        $this->assertEquals($domain->getFrom()->value(), $restoredDomain->getFrom()->value());
        $this->assertEquals($domain->getFromName()->value(), $restoredDomain->getFromName()->value());
        $this->assertEquals($domain->getTo()->value(), $restoredDomain->getTo()->value());
        $this->assertEquals($domain->getSubject()->value(), $restoredDomain->getSubject()->value());
        $this->assertEquals($domain->getBody()->value(), $restoredDomain->getBody()->value());
        $this->assertEquals($domain->getPayload()?->value(), $restoredDomain->getPayload()?->value());
        $this->assertEquals($domain->getAttempts()->value(), $restoredDomain->getAttempts()->value());
        $this->assertEquals($domain->getTraceId()->value(), $restoredDomain->getTraceId()->value());
        $this->assertEquals($domain->getScheduledAt()?->value(), $restoredDomain->getScheduledAt()?->value());
        $this->assertEquals($domain->getLockedAt()?->value(), $restoredDomain->getLockedAt()?->value());
        $this->assertEquals($domain->getErrorMessage()?->value(), $restoredDomain->getErrorMessage()?->value());
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

        $this->assertEquals($domain->getSubject()->value(), $orm->subject);
    }

    /**
     * @throws InvalidEmailSenderValueObjectException
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
     * @throws InvalidEmailSenderValueObjectException
     * @throws IncompatibleMappedEntityException
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmOutboxEmail());
    }
}
