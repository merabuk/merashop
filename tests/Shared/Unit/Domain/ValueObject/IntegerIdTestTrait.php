<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use PHPUnit\Framework\Assert;

trait IntegerIdTestTrait
{
    protected function assertValidIntegerId(string $className): void
    {
        $this->assertHasStaticMethod($className);

        $id = 123;
        $vo = $className::fromInt($id);

        Assert::assertSame($id, $vo->value());
        Assert::assertSame((string) $id, (string) $vo);
    }

    protected function assertIdEquality(string $className): void
    {
        $this->assertHasStaticMethod($className);

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

    private function assertHasStaticMethod(string $className): void
    {
        Assert::assertTrue(
            condition: method_exists($className, 'fromInt'),
            message: sprintf('%s class must implement fromInt method', $className)
        );
    }
}
