<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\EventHandler;

use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Bus\ExternalIntegrationEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsMessageHandler(fromTransport: TransportNameEnum::IdentityAccessOutbox->value)]
readonly class IdentityOutboxRelayHandler
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(ExternalIntegrationEvent $event): void
    {
        $this->eventBus->dispatch($event, [
            new TransportNamesStamp(TransportNameEnum::AmqpEvents->value),
        ]);
    }
}
