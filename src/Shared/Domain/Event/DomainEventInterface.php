<?php

namespace App\Shared\Domain\Event;

interface DomainEventInterface
{
    public function getEventName(): DomainEventNameEnum;
}
