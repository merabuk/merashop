<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Tracing;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\ValueObject\Tracing\TraceId;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class TraceIdTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validTraceIdProvider')]
    public function testItCreatesValidTraceId(string $traceId, string $excepted): void
    {
        $vo = TraceId::fromString($traceId);

        self::assertSame($excepted, $vo->value());
        self::assertSame($excepted, (string) $vo);
    }

    public static function validTraceIdProvider(): iterable
    {
        yield 'valid trace id' => ['01952796-03f3-793a-867c-d6159f8a329f', '01952796-03f3-793a-867c-d6159f8a329f'];
        yield 'trimmed' => ['   01952796-03f3-793a-867c-d6159f8a329f  ', '01952796-03f3-793a-867c-d6159f8a329f'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: TraceId::class,
            value: '01952796-03f3-793a-867c-d6159f8a329f',
            anotherValue: '01952796-03f3-793a-867c-d6159f8a3290'
        );
    }

    #[DataProvider('invalidTraceIdProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidTraceIdException::class);
        TraceId::fromString($invalidValue);
    }

    public static function invalidTraceIdProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong format' => ['01952796-03f3-493a-867c-d6159f8a3290'];
    }
}
