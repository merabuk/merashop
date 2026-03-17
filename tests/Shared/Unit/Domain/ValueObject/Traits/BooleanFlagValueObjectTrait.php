<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Shared\Domain\ValueObject\BaseFlag;
use PHPUnit\Framework\Assert;

trait BooleanFlagValueObjectTrait
{
    use ValueObjectEqualityCheckTrait;

    protected function assertCreatesValidFlag(
        string $className,
        bool $value,
        ?string $expectedStringValue = null,
    ): void {
        $this->assertHasStaticMethod($className, 'fromBool');

        $vo = $className::fromBool($value);

        $this->assertVoExtendsBaseFlagVo($vo);

        $expectedStringValue ??= $value ? BaseFlag::TRUE_STRING : BaseFlag::FALSE_STRING;

        Assert::assertSame($value, $vo->value());
        Assert::assertSame($expectedStringValue, (string) $vo);
    }

    protected function assertBooleanFlagEquality(string $className): void
    {
        $this->assertBooleanVOProvidesEqualityCheck(
            className: $className,
            value: true,
            anotherValue: false,
        );
    }

    protected function assertVoExtendsBaseFlagVo(object $vo): void
    {
        Assert::assertInstanceOf(BaseFlag::class, $vo);
    }
}
