<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use PHPUnit\Framework\Assert;
use RuntimeException;

trait IntegerIdTestTrait
{
    protected function assertValidIntegerId(string $className): void
    {
        $id = 123;

        if (!method_exists($className, 'fromInt')) {
            throw new RuntimeException(sprintf('%s class must implement fromInt method', $className));
        }

        $vo = $className::fromInt($id);

        Assert::assertSame($id, $vo->value());
        Assert::assertSame((string) $id, (string) $vo);
    }

    protected function assertIdEquality(string $className): void
    {
        if (!method_exists($className, 'fromInt')) {
            throw new RuntimeException(sprintf('%s class must implement fromInt method', $className));
        }

        $vo1 = $className::fromInt(123);
        $vo2 = $className::fromInt(123);
        $vo3 = $className::fromInt(321);

        Assert::assertTrue($vo1->equals($vo2));
        Assert::assertFalse($vo1->equals($vo3));
    }

    public static function invalidIdProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'zero' => [0];
    }
}
