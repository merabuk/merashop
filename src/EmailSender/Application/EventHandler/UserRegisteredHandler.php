<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\EmailSender\Application\Service\EmailQueueServiceInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::EmailSenderExternal->value)]
readonly class UserRegisteredHandler implements EventHandlerInterface
{
    public function __construct(
        private EmailQueueServiceInterface $notificationService,
        private string $appName,
    ) {
    }

    public function __invoke(UserRegisteredSharedEvent $event): void
    {
        $this->notificationService->queueEmail(
            emailType: $event->getRoutingKey(),
            to: $event->email,
            context: [
                'appName' => $this->appName,
                'userName' => 'Customer', // TODO: Refactor getting customer name or remove this parameter
            ],
        );
    }
}
