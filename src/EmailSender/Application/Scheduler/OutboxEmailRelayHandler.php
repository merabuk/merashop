<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Scheduler;

use App\EmailSender\Application\Service\OutboxEmailRelayServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class OutboxEmailRelayHandler
{
    public function __construct(
        private OutboxEmailRelayServiceInterface $relayService,
    ) {
    }

    public function __invoke(OutboxEmailRelayMessage $event): void
    {
        $this->relayService->execute();
    }
}
