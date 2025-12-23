<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

interface DomainEventInterface extends EventInterface
{
    public function getEventName(): DomainEventNameEnum;
}
