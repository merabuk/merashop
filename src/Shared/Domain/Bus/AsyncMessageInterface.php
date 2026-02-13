<?php

declare(strict_types=1);

namespace App\Shared\Domain\Bus;

use App\Shared\Domain\Event\EventInterface;

interface AsyncMessageInterface extends EventInterface
{
    public function getRoutingKey(): string;
}
