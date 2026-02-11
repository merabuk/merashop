<?php

declare(strict_types=1);

namespace App\Catalog\Application\EventHandler;

use App\Catalog\Domain\Event\CategoryMovedDomainEvent;
use App\Shared\Application\Bus\TransportNameEnum;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsMessageHandler(fromTransport: TransportNameEnum::CatalogOutbox->value)]
readonly class CategoryMovedOutboxHandler
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(CategoryMovedDomainEvent $event): void
    {
        $this->eventBus->dispatch($event, [
            new TransportNamesStamp(TransportNameEnum::CatalogInternal->value),
        ]);
    }
}
