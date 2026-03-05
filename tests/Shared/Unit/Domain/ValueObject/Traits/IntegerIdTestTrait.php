<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use PHPUnit\Framework\Assert;

trait IntegerIdTestTrait
{
    use ValueObjectEqualityCheckTrait;

    protected function assertValidIntegerId(string $className): void
    {
        $this->assertHasStaticMethod($className, 'fromInt');

        $id = 123;
        $vo = $className::fromInt($id);

        Assert::assertSame($id, $vo->value());
        Assert::assertSame((string) $id, (string) $vo);
    }

    protected function assertIdEquality(string $className): void
    {
        $this->assertIntegerVOProvidesEqualityCheck(
            className: $className,
            value: 123,
            anotherValue: 321
        );
    }

    public static function invalidIdProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'zero' => [0];
    }
}
