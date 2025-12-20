<?php

namespace App\Shared\Infrastructure\Messenger\Middleware;

use App\Shared\Domain\Event\DomainEventInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class EventRoutingKeyMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof DomainEventInterface) {
            $routingKey = $message->getEventName()->value;
            $envelope = $envelope->with(new AmqpStamp($routingKey));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
