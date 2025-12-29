<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Command\SendOutboxEmail;

use App\EmailSender\Domain\Enum\EmailSenderQueueEnum;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Domain\Bus\AsyncMessageInterface;

readonly class SendOutboxEmailCommand implements CommandInterface, AsyncMessageInterface
{
    public function __construct(
        public int $id,
    ) {
    }

    public function getRoutingKey(): string
    {
        return EmailSenderQueueEnum::EmailProcessor->value;
    }
}
