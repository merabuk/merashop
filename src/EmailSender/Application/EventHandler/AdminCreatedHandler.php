<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\EmailSender\Application\Service\EmailQueueServiceInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\AdminCreatedSharedEvent;
use App\Shared\Domain\Event\EventHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::EmailSenderExternal->value)]
readonly class AdminCreatedHandler implements EventHandlerInterface
{
    public function __construct(
        private EmailQueueServiceInterface $notificationService,
        private string $appName,
    ) {
    }

    public function __invoke(AdminCreatedSharedEvent $event): void
    {
        $this->notificationService->queueEmail(
            emailType: $event->getRoutingKey(),
            to: $event->email,
            context: [
                'appName' => $this->appName,
                'adminName' => 'Admin', // TODO: Refactor getting admin name or remove this parameter
                'temporaryPassword' => $event->temporaryPassword,
            ],
        );
    }
}
