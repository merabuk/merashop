<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\Tests\EmailSender\Support\Traits\EmailSenderEntityManagerTrait;
use App\Tests\EmailSender\Support\Traits\OutboxEmailFactoryTrait;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use App\Tests\Shared\Support\Traits\TransactionalTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;

final class OutboxEmailReadRepositoryTest extends KernelTestCase
{
    use EmailSenderEntityManagerTrait;
    use OutboxEmailFactoryTrait;
    use TraceIdHelperTrait;
    use TransactionalTrait;
    use ValueObjectAssertionTrait;

    private OutboxEmailReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(OutboxEmailReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $outboxEmail = $this->getOutboxEmailFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($outboxEmail->getId());

        self::assertNotNull($found);
        self::assertTrue($outboxEmail->getStatus()->equals($found->getStatus()));
        self::assertTrue($outboxEmail->getDriver()->equals($found->getDriver()));
        self::assertTrue($outboxEmail->getFrom()->equals($found->getFrom()));
        self::assertTrue($outboxEmail->getFromName()->equals($found->getFromName()));
        self::assertTrue($outboxEmail->getTo()->equals($found->getTo()));
        self::assertTrue($outboxEmail->getSubject()->equals($found->getSubject()));
        self::assertTrue($outboxEmail->getBody()->equals($found->getBody()));
        $this->assertVoEqualsOrNull($outboxEmail->getPayload(), $found->getPayload());
        self::assertTrue($outboxEmail->getAttempts()->equals($found->getAttempts()));
        $this->assertVoEqualsOrNull($outboxEmail->getTraceId(), $found->getTraceId());
        $this->assertVoEqualsOrNull($outboxEmail->getScheduledAt(), $found->getScheduledAt());
        $this->assertVoEqualsOrNull($outboxEmail->getLockedAt(), $found->getLockedAt());
        $this->assertVoEqualsOrNull($outboxEmail->getErrorMessage(), $found->getErrorMessage());
    }

    public function testFindByIdForUpdate(): void
    {
        $outboxEmail = $this->getOutboxEmailFixture()->create();
        $this->clearEntityManager();

        $em = $this->getEmailSenderEntityManager();

        $found = null;
        $this->executeInTransaction(
            em: $em,
            callback: function () use ($outboxEmail, &$found) {
                $found = $this->repository->findByIdForUpdate($outboxEmail->getId());
            }
        );

        self::assertNotNull($found);
        $this->assertVoEqualsOrNull($outboxEmail->getTraceId(), $found->getTraceId());
    }

    public function testFindReadyToProcess(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $limit = 10;
        $now = $clock->now();
        $staleTime = $now->modify('-10 minutes');

        self::assertCount(
            expectedCount: 0,
            haystack: $this->repository->findReadyToProcess(limit: $limit, now: $now, staleTime: $staleTime)
        );

        $fixture = $this->getOutboxEmailFixture();

        // valid
        $fixture->createCreatedEmail();
        // not valid
        $fixture->createSentEmail();
        $fixture->createLockedEmail(lockedAt: $now->modify('-1 minute'));
        $fixture->createFailedEmail(attempts: 2, nextAttemptAt: $now->modify('+1 minute'));

        $this->clearEntityManager();

        self::assertCount(
            expectedCount: 1,
            haystack: $this->repository->findReadyToProcess(limit: $limit, now: $now, staleTime: $staleTime)
        );
    }

    public function testExistsByTraceId(): void
    {
        $traceId = $this->getTraceId();

        self::assertFalse($this->repository->existsByTraceId($traceId));

        $this->getOutboxEmailFixture()->create(traceId: $traceId);
        $this->clearEntityManager();

        self::assertTrue($this->repository->existsByTraceId($traceId));
    }
}
