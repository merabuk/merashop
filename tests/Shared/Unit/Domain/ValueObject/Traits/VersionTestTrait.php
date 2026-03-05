<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use PHPUnit\Framework\Assert;

trait VersionTestTrait
{
    use ValueObjectEqualityCheckTrait;

    protected function assertValidVersion(string $className): void
    {
        $this->assertHasStaticMethod($className, 'fromInt');

        $version = 3;
        $vo = $className::fromInt($version);

        Assert::assertSame($version, $vo->value());
        Assert::assertSame((string) $version, (string) $vo);
    }

    protected function assertVersionEquality(string $className): void
    {
        $this->assertIntegerVOProvidesEqualityCheck(
            className: $className,
            value: 3,
            anotherValue: 2
        );
    }

    public static function invalidVersionProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'zero' => [0];
    }
}
