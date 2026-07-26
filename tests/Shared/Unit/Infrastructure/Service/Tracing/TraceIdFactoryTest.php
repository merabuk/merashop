<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Tracing;

use App\Shared\Domain\Service\Identity\UuidGeneratorInterface;
use App\Shared\Infrastructure\Service\Tracing\TraceIdFactory;
use App\Tests\Shared\BaseUnitTest;

final class TraceIdFactoryTest extends BaseUnitTest
{
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
