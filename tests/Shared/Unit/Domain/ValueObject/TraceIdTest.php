<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\ValueObject\TraceId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TraceIdTest extends TestCase
{
    /**
     * @throws InvalidTraceIdException
     */
    public function testItCreatesValidTraceId(): void
    {
        $traceId = '01952796-03f3-793a-867c-d6159f8a329f';
        $vo = TraceId::fromString($traceId);

        self::assertSame($traceId, $vo->value());
        self::assertSame($traceId, (string) $vo);
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function testItTrimsInput(): void
    {
        $traceId = '01952796-03f3-793a-867c-d6159f8a329f';
        $vo = TraceId::fromString('  '.$traceId.'  ');

        self::assertSame($traceId, $vo->value());
    }

    /**
     * @throws InvalidTraceIdException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');
        $vo2 = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a329f');
        $vo3 = TraceId::fromString('01952796-03f3-793a-867c-d6159f8a3290');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
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
