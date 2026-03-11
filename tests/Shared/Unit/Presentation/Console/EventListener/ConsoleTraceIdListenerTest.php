<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Console\EventListener;

use App\Shared\Domain\Service\Tracing\TraceIdContextInterface;
use App\Shared\Domain\Service\Tracing\TraceIdFactoryInterface;
use App\Shared\Presentation\Console\EventListener\ConsoleTraceIdListener;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleTraceIdListenerTest extends TestCase
{
    use TraceIdHelperTrait;

    public function testItSetsTraceId(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new ConsoleTraceIdListener(traceIdContext: $context, traceIdFactory: $factory);

        $traceId = $this->getTraceId();

        $factory->expects(self::once())
            ->method('createNew')
            ->willReturn($traceId);

        $context->expects(self::once())->method('set')->with($traceId);

        $event = new ConsoleCommandEvent(
            command: $this->createMock(Command::class),
            input: $this->createMock(InputInterface::class),
            output: $this->createMock(OutputInterface::class)
        );
        $listener->onConsoleCommand($event);
    }
}
