<?php

declare(strict_types=1);

namespace App\EmailSender\Application\EventHandler;

use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Domain\Event\EventHandlerInterface;
use App\Shared\Domain\Event\UserRegisteredSharedEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Event->value)]
class UserRegisteredHandler implements EventHandlerInterface
{
    public function __invoke(UserRegisteredSharedEvent $event): void
    {
    }
}
