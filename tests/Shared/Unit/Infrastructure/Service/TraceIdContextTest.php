<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\TraceIdFactoryInterface;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Service\TraceIdContext;
use PHPUnit\Framework\TestCase;

final class TraceIdContextTest extends TestCase
{
    /**
     * @throws InvalidTraceIdException
     */
    public function testItSetsAndGetsTraceId(): void
    {
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $context = new TraceIdContext($factory);
        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $context->set($traceId);

        self::assertSame($traceId, $context->get());
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function testItGeneratesNewIdIfNoneSet(): void
    {
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $newTraceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $factory->expects(self::once())
            ->method('createNew')
            ->willReturn($newTraceId);

        $context = new TraceIdContext($factory);

        self::assertSame($newTraceId, $context->get());
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function testItResetsState(): void
    {
        $factory = $this->createMock(TraceIdFactoryInterface::class);
        $context = new TraceIdContext($factory);
        $traceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');

        $context->set($traceId);
        self::assertSame($traceId, $context->get());

        $context->reset();

        $newTraceId = TraceId::fromString('01952796-03f3-793a-867c-d6159f8b0000');

        $factory->expects(self::once())->method('createNew')->willReturn($newTraceId);

        self::assertSame($newTraceId->value(), $context->get()->value());
    }
}
