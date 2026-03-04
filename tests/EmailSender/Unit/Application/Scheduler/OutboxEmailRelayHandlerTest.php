<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\Scheduler;

use App\EmailSender\Application\Scheduler\OutboxEmailRelayHandler;
use App\EmailSender\Application\Scheduler\OutboxEmailRelayMessage;
use App\EmailSender\Application\Service\OutboxEmailRelayServiceInterface;
use PHPUnit\Framework\TestCase;

final class OutboxEmailRelayHandlerTest extends TestCase
{
    private OutboxEmailRelayServiceInterface $relayService;

    public function setUp(): void
    {
        $this->relayService = $this->createMock(OutboxEmailRelayServiceInterface::class);
    }

    public function testItInvokesRelayServiceCorrectly(): void
    {
        $this->relayService->expects(self::once())->method('execute');

        $event = new OutboxEmailRelayMessage();
        $this->createScheduler()($event);
    }

    private function createScheduler(): OutboxEmailRelayHandler
    {
        return new OutboxEmailRelayHandler($this->relayService);
    }
}
