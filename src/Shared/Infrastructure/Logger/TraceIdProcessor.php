<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logger;

use App\Shared\Domain\Service\TraceIdContextInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('monolog.processor')]
readonly class TraceIdProcessor implements ProcessorInterface
{
    public function __construct(
        private TraceIdContextInterface $traceIdContext,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['traceId'] = $this->traceIdContext->get()->value();

        return $record;
    }
}
