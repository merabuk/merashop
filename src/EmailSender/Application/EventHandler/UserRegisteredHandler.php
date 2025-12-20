<?php

namespace App\EmailSender\Application\EventHandler;

use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\Event\DomainEventNameEnum;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value)]
class UserRegisteredHandler implements DomainEventInterface
{
    public function __invoke(UserRegisteredSharedEvent $event): void
    {
    }

    public function getEventName(): DomainEventNameEnum
    {
        return DomainEventNameEnum::UserRegistered;
    }
}
