<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\EventHandler;

use App\IdentityAccess\Application\EventHandler\IdentityOutboxRelayHandler;
use App\Shared\Application\Bus\TransportNameEnum;
use App\Shared\Domain\Bus\ExternalIntegrationEvent;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class IdentityOutboxRelayHandlerTest extends TestCase
{
    public function testItShouldRelayEvents(): void
    {
        $eventBus = $this->createMock(MessageBusInterface::class);

        $event = new class implements ExternalIntegrationEvent {
            public function getRoutingKey(): string
            {
                return 'test_routing_key';
            }
        };

        $eventBus->expects($this->once())
            ->method('dispatch')
            ->with(
                self::identicalTo($event),
                $this->isAmqpTransportStamp()
            )
            ->willReturn(new Envelope($event));

        $handler = new IdentityOutboxRelayHandler($eventBus);
        $handler($event);
    }

    private function isAmqpTransportStamp(): Callback
    {
        return self::callback(function (array $stamps) {
            $hasCorrectTransport = false;

            foreach ($stamps as $stamp) {
                if ($stamp instanceof TransportNamesStamp) {
                    if (in_array(TransportNameEnum::AmqpEvents->value, $stamp->getTransportNames(), true)) {
                        $hasCorrectTransport = true;
                        break;
                    }
                }
            }

            return $hasCorrectTransport;
        });
    }
}
