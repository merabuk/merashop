<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\EmailSender\Application\Command\SendOutboxEmail\SendOutboxEmailCommand;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\EmailSender\Domain\Service\OutboxEmailFactoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use App\Shared\Domain\Service\TraceIdContextInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler(bus: BusNameEnum::Event->value)]
class UserRegisteredHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly TraceIdContextInterface $traceIdContext,
        private readonly OutboxEmailFactoryInterface $emailFactory,
        private readonly OutboxEmailWriteRepositoryInterface $outboxEmailWriteRepository,
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(UserRegisteredSharedEvent $event): void
    {
        $email = $this->emailFactory->createFromTemplate(
            to: $event->email,
            subject: 'Welcome to Merashop',
            template: 'emails/signup.html.twig',
            context: ['userName' => $event->name],
            traceId: $this->traceIdContext->get()
        );

        $this->outboxEmailWriteRepository->save($email);

        $this->commandBus->dispatch(
            message: new SendOutboxEmailCommand($email->getId()->value()),
            stamps: [new DispatchAfterCurrentBusStamp()]
        );
    }
}
