<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Service\ContentProvider\EmailContentProviderInterface;
use App\EmailSender\Application\Service\EmailQueueService;
use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Factory\Contract\OutboxEmailFactoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\Shared\Domain\Service\Tracing\TraceIdContextInterface;
use App\Tests\EmailSender\Support\OutboxEmailMother;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Exception\RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final class EmailQueueServiceTest extends TestCase
{
    use TraceIdHelperTrait;

    private TraceIdContextInterface $traceIdContext;
    private OutboxEmailReadRepositoryInterface $readRepository;
    private LoggerInterface $logger;
    private ContainerInterface $container;
    private OutboxEmailFactoryInterface $factory;
    private OutboxEmailWriteRepositoryInterface $writeRepository;
    private MessageBusInterface $commandBus;

    public function setUp(): void
    {
        $this->traceIdContext = $this->createMock(TraceIdContextInterface::class);
        $this->readRepository = $this->createMock(OutboxEmailReadRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = $this->createMock(OutboxEmailFactoryInterface::class);
        $this->writeRepository = $this->createMock(OutboxEmailWriteRepositoryInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
    }

    public function testItQueuedEmailsCorrectly(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $subject = 'subject';
        $template = '<html lang="en">Template</html>';
        $traceId = $this->getTraceId();
        $fakeId = 123;

        $this->traceIdContext->expects(self::once())->method('get')->willReturn($traceId);
        $this->readRepository->expects(self::once())->method('existsByTraceId')->willReturn(false);

        $provider = $this->createMock(EmailContentProviderInterface::class);
        $provider->expects(self::once())->method('getSubject')->willReturn($subject);
        $provider->expects(self::once())->method('getTemplate')->willReturn($template);

        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($emailType))
            ->willReturn(true);
        $this->container->expects(self::once())
            ->method('get')
            ->with(self::equalTo($emailType))
            ->willReturn($provider);

        $this->factory->expects(self::once())
            ->method('createFromTemplate')
            ->with(
                self::equalTo($to),
                self::equalTo($subject),
                self::equalTo($template),
                self::equalTo($context),
                self::equalTo($traceId),
            )
            ->willReturn(OutboxEmailMother::createWithData(
                to: $to,
                subject: $subject,
                body: $template,
                payload: $context,
                traceId: $traceId,
            ));

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (OutboxEmail $passedEmail): bool {
                return null === $passedEmail->getId();
            }))
            ->willReturn(OutboxEmailMother::createWithData(
                to: $to,
                subject: $subject,
                body: $template,
                payload: $context,
                traceId: $traceId,
                id: $fakeId,
            ));

        $this->commandBus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(function (SendOutboxEmailCommand $command) use ($fakeId): bool {
                    return $command->id === $fakeId;
                }),
                self::callback(function (array $stamps): bool {
                    return 1 === count($stamps) && $stamps[0] instanceof DispatchAfterCurrentBusStamp;
                })
            );

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    public function testItDoesNotQueueEmailIfTraceIdExists(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $traceId = $this->getTraceId();

        $this->traceIdContext->method('get')->willReturn($traceId);
        $this->readRepository->method('existsByTraceId')->willReturn(true);
        $this->logger->expects(self::once())
            ->method('info')
            ->with(
                self::equalTo(sprintf('Outbox email already queued with trace id: %s', $traceId)),
            );

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    public function testItDoesNotQueueEmailIfContainerDoesNotHaveProvider(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $traceId = $this->getTraceId();

        $this->traceIdContext->method('get')->willReturn($traceId);
        $this->readRepository->method('existsByTraceId')->willReturn(false);

        $this->container->method('has')->willReturn(false);

        $this->logger->expects(self::once())
            ->method('error')
            ->with(self::equalTo(sprintf('Email provider "%s" not found', $emailType)));

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    public function testItDoesNotQueueEmailIfProviderIsNotAnInstanceOfEmailContentProviderInterface(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $traceId = $this->getTraceId();

        $this->traceIdContext->method('get')->willReturn($traceId);
        $this->readRepository->method('existsByTraceId')->willReturn(false);

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn(new stdClass());

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                self::equalTo(sprintf(
                    'Email provider "%s" is not an instance of %s',
                    $emailType,
                    EmailContentProviderInterface::class
                ))
            );

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    public function testItDoesNotQueueEmailWhenFailedToMakeWithFactory(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $traceId = $this->getTraceId();

        $this->traceIdContext->method('get')->willReturn($traceId);
        $this->readRepository->method('existsByTraceId')->willReturn(false);

        $provider = $this->createMock(EmailContentProviderInterface::class);
        $provider->method('getSubject')->willReturn('subject');
        $provider->method('getTemplate')->willReturn('template');

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($provider);

        $this->factory->method('createFromTemplate')
            ->willThrowException(new Exception('Failed to make email'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                self::equalTo(sprintf('Failed to queue email "%s": Failed to make email', $emailType)),
                self::logicalAnd(
                    self::arrayHasKey('trace_id'),
                    self::arrayHasKey('exception_class')
                )
            );

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    public function testItDoesNotQueueEmailWhenTransportThrowsException(): void
    {
        $emailType = 'event_route_key';
        $to = 'recipient@example.com';
        $context = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $subject = 'subject';
        $template = '<html lang="en">Template</html>';
        $traceId = $this->getTraceId();
        $fakeId = 123;

        $this->traceIdContext->method('get')->willReturn($traceId);
        $this->readRepository->method('existsByTraceId')->willReturn(false);

        $provider = $this->createMock(EmailContentProviderInterface::class);
        $provider->method('getSubject')->willReturn($subject);
        $provider->method('getTemplate')->willReturn($template);

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($provider);

        $outboxEmail = OutboxEmailMother::createWithData(
            to: $to,
            subject: $subject,
            body: $template,
            payload: $context,
            traceId: $traceId,
            id: $fakeId,
        );

        $this->factory->method('createFromTemplate')->willReturn($outboxEmail);

        $this->writeRepository->method('save')->willReturn($outboxEmail);

        $this->commandBus->method('dispatch')
            ->willThrowException(new RuntimeException('Failed to send message'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                self::equalTo(sprintf('Failed to queue email "%s": Failed to send message', $emailType)),
                self::logicalAnd(
                    self::arrayHasKey('trace_id'),
                    self::arrayHasKey('exception_class')
                )
            );

        $this->createService()->queueEmail(emailType: $emailType, to: $to, context: $context);
    }

    private function createService(): EmailQueueService
    {
        return new EmailQueueService(
            traceIdContext: $this->traceIdContext,
            readRepository: $this->readRepository,
            logger: $this->logger,
            providers: $this->container,
            emailFactory: $this->factory,
            writeRepository: $this->writeRepository,
            commandBus: $this->commandBus,
        );
    }
}
