<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Tests\EmailSender\Support\Traits\EmailSenderEntityManagerTrait;
use App\Tests\EmailSender\Support\Traits\OutboxEmailFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OutboxEmailWriteRepositoryTest extends KernelTestCase
{
    use EmailSenderEntityManagerTrait;
    use OutboxEmailFactoryTrait;
    use EntityTechnicalMetadataTrait;
    use ValueObjectAssertionTrait;

    private OutboxEmailWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(OutboxEmailWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $email = $this->getOutboxEmailMother()->createCreatedEmail();

        $created = $this->repository->save($email);

        self::assertNotNull($created->getId());
        self::assertTrue($email->getStatus()->equals($created->getStatus()));
        self::assertTrue($email->getDriver()->equals($created->getDriver()));
        self::assertTrue($email->getFrom()->equals($created->getFrom()));
        self::assertTrue($email->getFromName()->equals($created->getFromName()));
        self::assertTrue($email->getTo()->equals($created->getTo()));
        self::assertTrue($email->getSubject()->equals($created->getSubject()));
        self::assertTrue($email->getBody()->equals($created->getBody()));
        $this->assertVoEqualsOrNull($email->getPayload(), $created->getPayload());
        self::assertTrue($email->getAttempts()->equals($created->getAttempts()));
        $this->assertVoEqualsOrNull($email->getTraceId(), $created->getTraceId());
        $this->assertVoEqualsOrNull($email->getScheduledAt(), $created->getScheduledAt());
        $this->assertVoEqualsOrNull($email->getLockedAt(), $created->getLockedAt());
        $this->assertVoEqualsOrNull($email->getErrorMessage(), $created->getErrorMessage());
    }

    public function testDelete(): void
    {
        $email = $this->getOutboxEmailFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($email);

        $readRepository = self::getContainer()->get(OutboxEmailReadRepositoryInterface::class);

        self::assertNull($readRepository->findById($email->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $email = $this->getOutboxEmailFixture()->create();
        $id = $email->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmOutboxEmail::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }
}
