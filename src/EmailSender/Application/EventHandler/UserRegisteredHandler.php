<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\OutboxEmailFactoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Shared\Domain\Service\TraceIdContextInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::EmailSenderExternal->value)]
class UserRegisteredHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly TraceIdContextInterface $traceIdContext,
        private readonly OutboxEmailFactoryInterface $emailFactory,
        private readonly TranslatorInterface $translator,
        private readonly OutboxEmailReadRepositoryInterface $outboxEmailReadRepository,
        private readonly OutboxEmailWriteRepositoryInterface $outboxEmailWriteRepository,
        private readonly MessageBusInterface $commandBus,
        private readonly LoggerInterface $logger,
        private readonly string $appName,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(UserRegisteredSharedEvent $event): void
    {
        $traceId = $this->traceIdContext->get();

        if ($this->outboxEmailReadRepository->existsByTraceId($traceId)) {
            $this->logger->info('User already have welcome email');

            return;
        }

        $email = $this->emailFactory->createFromTemplate(
            to: $event->email,
            subject: $this->translator->trans(
                id: 'welcome',
                parameters: ['appName' => $this->appName],
                domain: 'emails'.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX
            ),
            template: 'emails/signup.html.twig',
            context: ['userName' => 'Customer'], // TODO: Refactor getting customer name or remove this parameter
            traceId: $traceId
        );

        $email = $this->outboxEmailWriteRepository->save($email);

        $id = $email->getId()?->value() ?? throw new RuntimeException('Outbox email does not have an ID');

        $this->commandBus->dispatch(
            message: new SendOutboxEmailCommand($id),
            stamps: [new DispatchAfterCurrentBusStamp()]
        );
    }
}
