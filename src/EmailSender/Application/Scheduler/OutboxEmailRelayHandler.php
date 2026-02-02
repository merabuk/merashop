<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Scheduler;

use App\EmailSender\Application\Service\OutboxEmailRelayService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsMessageHandler]
readonly class OutboxEmailRelayHandler
{
    public function __construct(
        private OutboxEmailRelayService $relayService,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ExceptionInterface
     */
    public function __invoke(OutboxEmailRelayMessage $event): void
    {
        $this->relayService->execute();
    }
}
