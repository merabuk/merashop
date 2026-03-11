<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Tracing;

use App\Shared\Domain\Service\Tracing\TraceIdFactoryInterface;
use App\Shared\Infrastructure\Service\Tracing\TraceIdContext;
use App\Tests\Shared\Support\Traits\TraceIdHelperTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TraceIdContextTest extends TestCase
{
    use TraceIdHelperTrait;

    private TraceIdFactoryInterface&MockObject $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(TraceIdFactoryInterface::class);
    }

    public function testItSetsAndGetsTraceId(): void
    {
        $context = $this->createContext();
        $traceId = $this->getTraceId();

        $context->set($traceId);

        self::assertSame($traceId, $context->get());
    }

    public function testItGeneratesNewIdIfNoneSet(): void
    {
        $newTraceId = $this->getTraceId();

        $this->factory->expects(self::once())
            ->method('createNew')
            ->willReturn($newTraceId);

        $context = $this->createContext();

        self::assertSame($newTraceId, $context->get());
    }

    public function testItResetsState(): void
    {
        $context = $this->createContext();
        $traceId = $this->getTraceId();

        $context->set($traceId);
        self::assertSame($traceId, $context->get());

        $context->reset();

        $newTraceId = $this->getTraceId('01952796-03f3-793a-867c-d6159f8b0000');

        $this->factory->expects(self::once())->method('createNew')->willReturn($newTraceId);

        self::assertSame($newTraceId->value(), $context->get()->value());
    }

    private function createContext(): TraceIdContext
    {
        return new TraceIdContext(traceIdFactory: $this->factory);
    }
}
