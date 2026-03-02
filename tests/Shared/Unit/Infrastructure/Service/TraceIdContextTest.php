<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Domain\Exception\Services\TraceIdFactoryException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Service\TraceIdContext;
use PHPUnit\Framework\TestCase;

final class TraceIdContextTest extends TestCase
{
    private TraceIdFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(TraceIdFactoryInterface::class);
    }

    /**
     * @throws InvalidTraceIdException
     * @throws TraceIdFactoryException
     */
    public function testItSetsAndGetsTraceId(): void
    {
        $context = $this->createContext();
        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $context->set($traceId);

        self::assertSame($traceId, $context->get());
    }

    /**
     * @throws InvalidTraceIdException
     * @throws TraceIdFactoryException
     */
    public function testItGeneratesNewIdIfNoneSet(): void
    {
        $newTraceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $this->factory->expects(self::once())
            ->method('createNew')
            ->willReturn($newTraceId);

        $context = $this->createContext();

        self::assertSame($newTraceId, $context->get());
    }

    /**
     * @throws InvalidTraceIdException
     * @throws TraceIdFactoryException
     */
    public function testItResetsState(): void
    {
        $context = $this->createContext();
        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $context->set($traceId);
        self::assertSame($traceId, $context->get());

        $context->reset();

        $newTraceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8b0000');

        $this->factory->expects(self::once())->method('createNew')->willReturn($newTraceId);

        self::assertSame($newTraceId->value(), $context->get()->value());
    }

    private function createContext(): TraceIdContext
    {
        return new TraceIdContext(traceIdFactory: $this->factory);
    }
}
