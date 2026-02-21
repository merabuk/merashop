<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Infrastructure\Service\TraceIdFactory;
use PHPUnit\Framework\TestCase;

final class TraceIdFactoryTest extends TestCase
{
    /**
     * @throws InvalidTraceIdException
     */
    public function testItCreatesNewTraceId(): void
    {
        $uuid = '01952796-03f3-793a-867c-d6159f8a329f';
        $generator = $this->createMock(UuidGeneratorInterface::class);
        $generator->method('nextV7')->willReturn($uuid);

        $factory = new TraceIdFactory($generator);
        $traceId = $factory->createNew();

        self::assertSame($uuid, $traceId->value());
    }
}
