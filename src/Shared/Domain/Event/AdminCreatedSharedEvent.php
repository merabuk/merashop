<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Bus\ExternalIntegrationEvent;
use App\Shared\Domain\Enum\SharedEventNameEnum;

readonly class AdminCreatedSharedEvent implements ExternalIntegrationEvent
{
    public function __construct(
        public string $id,
        public string $email,
        // TODO: rework on direct api call for better security
        public string $temporaryPassword,
    ) {
    }

    public function getRoutingKey(): string
    {
        return SharedEventNameEnum::AdminCreated->value;
    }
}
