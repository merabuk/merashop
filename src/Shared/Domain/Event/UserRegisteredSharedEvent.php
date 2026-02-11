<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Bus\AsyncMessageInterface;
use App\Shared\Domain\Enum\SharedEventNameEnum;

readonly class UserRegisteredSharedEvent implements AsyncMessageInterface
{
    public function __construct(
        public string $id,
        public string $email,
    ) {
    }

    public function getRoutingKey(): string
    {
        return SharedEventNameEnum::UserRegistered->value;
    }
}
