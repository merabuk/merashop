<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\EmailSender\Application\Service\EmailQueueService;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Event\AdminCreatedSharedEvent;
use App\Shared\Domain\Event\EventHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsMessageHandler(bus: BusNameEnum::Event->value, fromTransport: TransportNameEnum::EmailSenderExternal->value)]
readonly class AdminCreatedHandler implements EventHandlerInterface
{
    public function __construct(
        private EmailQueueService $notificationService,
        private string $appName,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
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
