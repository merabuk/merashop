<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Console\EventListener;

use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdContextInterface;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Presentation\Console\EventListener\ConsoleTraceIdListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleTraceIdListenerTest extends TestCase
{
    /**
     * @throws InvalidTraceIdException
     * @throws TraceIdFactoryException
     */
    public function testItSetsTraceId(): void
    {
        $context = $this->createMock(TraceIdContextInterface::class);
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $listener = new ConsoleTraceIdListener(traceIdContext: $context, traceIdFactory: $factory);

        $traceIdValue = '01952796-03f3-793a-867c-d6159f8a329f';
        $traceId = TraceId::fromString($traceIdValue);

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
