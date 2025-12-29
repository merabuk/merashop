<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\TraceId;

use App\Shared\Domain\Service\TraceIdContextInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class TraceIdMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TraceIdContextInterface $context,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $traceIdStamp = $envelope->last(TraceIdStamp::class);

        if ($traceIdStamp instanceof TraceIdStamp) {
            $this->context->set($traceIdStamp->traceId);
        }

        if (!$traceIdStamp) {
            $envelope = $envelope->with(new TraceIdStamp($this->context->get()));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
