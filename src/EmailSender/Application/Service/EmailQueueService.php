<?php

namespace App\EmailSender\Application\Service;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Application\Service\ContentProvider\EmailContentProviderInterface;
use App\EmailSender\Domain\Factory\Contract\OutboxEmailFactoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\Shared\Domain\Service\TraceIdContextInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Throwable;

final readonly class EmailQueueService implements EmailQueueServiceInterface
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
     */
    public function queueEmail(string $emailType, string $to, array $context): void
    {
        $traceId = $this->traceIdContext->get();

        if ($this->readRepository->existsByTraceId($traceId)) {
            $this->logger->info(sprintf('Outbox email already queued with trace id: %s', $traceId));

            return;
        }

        $provider = $this->getProvider($emailType);
        if (null === $provider) {
            return;
        }

        try {
            $email = $this->emailFactory->createFromTemplate(
                to: $to,
                subject: $provider->getSubject($context),
                template: $provider->getTemplate(),
                context: $context,
                traceId: $traceId
            );
            $email = $this->writeRepository->save($email);

            $id = $email->getId()?->value() ?? throw new RuntimeException('ID missing');

            $this->commandBus->dispatch(
                new SendOutboxEmailCommand($id),
                [new DispatchAfterCurrentBusStamp()]
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Failed to queue email "%s": %s', $emailType, $e->getMessage()), [
                'trace_id' => $traceId,
                'exception_class' => get_class($e),
            ]);
        }
    }

    private function getProvider(string $type): ?EmailContentProviderInterface
    {
        try {
            if (!$this->providers->has($type)) {
                $this->logger->error(sprintf('Email provider "%s" not found', $type));

                return null;
            }

            $provider = $this->providers->get($type);
            if (!$provider instanceof EmailContentProviderInterface) {
                $this->logger->error(sprintf(
                    'Email provider "%s" is not an instance of %s',
                    $type,
                    EmailContentProviderInterface::class
                ));

                return null;
            }

            return $provider;
        } catch (ContainerExceptionInterface $e) {
            $this->logger->error(sprintf('Failed to get email provider: %s', $e->getMessage()), [
                'exception_class' => get_class($e),
            ]);

            return null;
        }
    }
}
