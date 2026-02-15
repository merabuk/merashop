<?php

namespace App\EmailSender\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Service\ContentProvider\EmailContentProviderInterface;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\OutboxEmailFactoryInterface;
use App\Shared\Domain\Service\TraceIdContextInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class EmailQueueService
{
    public function __construct(
        private TraceIdContextInterface $traceIdContext,
        private OutboxEmailReadRepositoryInterface $readRepository,
        private LoggerInterface $logger,
        #[AutowireLocator(
            services: 'email_sender.email_content_provider',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
        private OutboxEmailFactoryInterface $emailFactory,
        private OutboxEmailWriteRepositoryInterface $writeRepository,
        private MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ExceptionInterface
     */
    public function queueEmail(
        string $emailType,
        string $to,
        array $context,
    ): void {
        $traceId = $this->traceIdContext->get();

        if ($this->readRepository->existsByTraceId($traceId)) {
            $this->logger->info(sprintf('Outbox email already queued with trace id: %s', $traceId));

            return;
        }

        if (!$this->providers->has($emailType)) {
            $this->logger->error(sprintf('Email provider "%s" not found', $emailType));

            return;
        }

        try {
            $provider = $this->providers->get($emailType);

            if (!$provider instanceof EmailContentProviderInterface) {
                $this->logger->error(sprintf(
                    'Email provider "%s" is not an instance of %s',
                    $emailType,
                    EmailContentProviderInterface::class
                ));

                return;
            }

            $subject = $provider->getSubject($context);
            $template = $provider->getTemplate();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $this->logger->error(sprintf('Email provider "%s" not found', $emailType));

            return;
        }

        $email = $this->emailFactory->createFromTemplate(
            to: $to,
            subject: $subject,
            template: $template,
            context: $context,
            traceId: $traceId
        );
        $email = $this->writeRepository->save($email);

        $id = $email->getId()?->value() ?? throw new RuntimeException('Outbox email does not have an ID');

        $this->commandBus->dispatch(
            new SendOutboxEmailCommand($id),
            [new DispatchAfterCurrentBusStamp()]
        );
    }
}
