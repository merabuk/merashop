<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\Command\SendOutboxEmail;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommandHandler;
use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\MailerServiceInterface;
use App\EmailSender\Domain\Service\OutboxRetryPolicy;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

final class SendOutboxEmailCommandHandlerTest extends TestCase
{
    private OutboxEmailReadRepositoryInterface $readRepository;
    private OutboxEmailWriteRepositoryInterface $writeRepository;
    private MailerServiceInterface $mailer;
    private LoggerInterface $logger;
    private OutboxRetryPolicy $retryPolicy;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(OutboxEmailReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(OutboxEmailWriteRepositoryInterface::class);
        $this->mailer = $this->createMock(MailerServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->retryPolicy = new OutboxRetryPolicy(3);
    }

    public function testItSuccessfullySendsOutboxEmail(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $fakeId = 123;

        $email = OutboxEmailMother::makeCreatedEmail(id: $fakeId);

        $this->readRepository->expects(self::once())->method('findByIdForUpdate')->willReturn($email);
        $this->writeRepository->expects(self::exactly(2))
            ->method('save')
            ->with(self::callback(function (OutboxEmail $passedEmail) use (&$callCount, $clock): bool {
                ++$callCount;
                if (1 === $callCount) {
                    return $passedEmail->getStatus()->isProcessing()
                        && $passedEmail->getLockedAt()->equalsWithDateTime($clock->now());
                }
                if (2 === $callCount) {
                    return $passedEmail->getStatus()->isSent()
                        && null === $passedEmail->getLockedAt();
                }

                return true;
            }))
            ->willReturn($email);

        $this->mailer->expects(self::once())
            ->method('process')
            ->with(
                self::callback(fn (OutboxEmail $email) => $email->getId()->value() === $fakeId
                    && $email->getLockedAt()->equalsWithDateTime($clock->now()))
            );

        $command = new SendOutboxEmailCommand(id: $fakeId);

        $this->createHandler($clock)($command);

        self::assertTrue($email->getStatus()->isSent());
        self::assertNull($email->getLockedAt());
    }

    public function testItFailsToSendOutboxEmailAndHaveAttempts(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $fakeId = 123;

        $attempts = 1;
        $email = OutboxEmailMother::makeFailedEmail(
            attempts: $attempts,
            nextAttemptAt: $clock->now()->modify('-1 seconds'),
            id: $fakeId
        );

        $this->readRepository->expects(self::once())->method('findByIdForUpdate')->willReturn($email);
        $this->writeRepository->expects(self::exactly(2))
            ->method('save')
            ->with(self::callback(function (OutboxEmail $passedEmail) use (&$callCount, $clock, $attempts): bool {
                ++$callCount;
                if (2 === $callCount) {
                    $expectedAttempts = $attempts + 1;

                    return $passedEmail->getStatus()->isFailed()
                        && $expectedAttempts === $passedEmail->getAttempts()->value()
                        && null === $passedEmail->getLockedAt()
                        && $passedEmail->getScheduledAt()->equalsWithDateTime($clock->now()->modify(sprintf('+%d minutes', $expectedAttempts ** 2)))
                        && 'SMTP Timeout' === $passedEmail->getErrorMessage()->value();
                }

                return true;
            }))
            ->willReturn($email);

        $this->mailer->expects(self::once())
            ->method('process')
            ->willThrowException(new Exception('SMTP Timeout'));

        $command = new SendOutboxEmailCommand(id: $fakeId);

        $this->createHandler($clock)($command);

        self::assertTrue($email->getStatus()->isFailed());
    }

    public function testItFailsToSendOutboxEmailAndReachMaxAttempts(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $fakeId = 123;

        $email = OutboxEmailMother::makeFailedEmail(
            attempts: $this->retryPolicy->getMaxAttempts() - 1,
            nextAttemptAt: $clock->now()->modify('-1 seconds'),
            id: $fakeId
        );

        $this->readRepository->expects(self::once())->method('findByIdForUpdate')->willReturn($email);
        $this->writeRepository->expects(self::exactly(2))
            ->method('save')
            ->with(self::callback(function (OutboxEmail $passedEmail) use (&$callCount): bool {
                ++$callCount;
                if (2 === $callCount) {
                    return $passedEmail->getStatus()->isFailedPermanently()
                        && $passedEmail->getAttempts()->value() === $this->retryPolicy->getMaxAttempts()
                        && null === $passedEmail->getLockedAt()
                        && null === $passedEmail->getScheduledAt()
                        && 'Permanent Error' === $passedEmail->getErrorMessage()->value();
                }

                return true;
            }))
            ->willReturn($email);

        $this->mailer->expects(self::once())
            ->method('process')
            ->willThrowException(new Exception('Permanent Error'));
        $this->logger->expects(self::once())
            ->method('critical')
            ->with(
                self::equalTo('Email sending failed permanently'),
                self::logicalAnd(
                    self::arrayHasKey('id'),
                    self::arrayHasKey('trace_id'),
                    self::arrayHasKey('error')
                )
            );

        $command = new SendOutboxEmailCommand(id: $fakeId);
        $this->createHandler($clock)($command);

        self::assertTrue($email->getStatus()->isFailedPermanently());
        self::assertSame($this->retryPolicy->getMaxAttempts(), $email->getAttempts()->value());
    }

    #[DataProvider('skipSendingProvider')]
    public function testItSkipsSendingOutboxEmail(?OutboxEmail $email): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');

        $this->readRepository->expects(self::once())->method('findByIdForUpdate')->willReturn($email);

        $this->logger->expects(self::once())->method('notice');

        $command = new SendOutboxEmailCommand(id: 123);
        $this->createHandler($clock)($command);
    }

    public static function skipSendingProvider(): iterable
    {
        yield 'when not found' => [null];
        yield 'already sent' => [OutboxEmailMother::makeSentEmail()];
        yield 'already locker' => [OutboxEmailMother::makeLockedEmail()];
        yield 'delay time not reached' => [OutboxEmailMother::makeFailedEmail(
            nextAttemptAt: new DateTimeImmutable('2024-01-01 10:00:00')->modify('+1 minutes')
        )];
    }

    private function createHandler(?ClockInterface $clock = null): SendOutboxEmailCommandHandler
    {
        $clock ??= new MockClock();

        return new SendOutboxEmailCommandHandler(
            readRepository: $this->readRepository,
            writeRepository: $this->writeRepository,
            mailer: $this->mailer,
            logger: $this->logger,
            retryPolicy: $this->retryPolicy,
            clock: $clock
        );
    }
}
