<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Tests\Shared\Support\Traits\EmailTestDataTrait;
use PHPUnit\Framework\Assert;

trait EmailAddressTestTrait
{
    use EmailTestDataTrait;
    use ValueObjectEqualityCheckTrait;

    protected function assertValidEmailAddress(string $className, string $email, string $expected): void
    {
        $this->assertHasStaticMethod($className, 'fromString');

        $vo = $className::fromString($email);

        Assert::assertSame($expected, $vo->value());
        Assert::assertSame($expected, (string) $vo);
    }

    protected function assertEmailAddressEquality(string $className): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: $className,
            value: 'test@example.com',
            anotherValue: 'different@example.com',
        );
    }

    public static function validEmailSampleProvider(): iterable
    {
        yield 'simple' => ['test@example.com', 'test@example.com'];
        yield 'trimmed' => ['  test@example.com  ', 'test@example.com'];
    }

    public static function invalidEmailSampleProvider(): iterable
    {
        yield 'wrong format' => ['not-an-email'];
        yield 'local part too long' => [self::createEmailWithLongLocalPart()];
        yield 'total length exceeded' => [self::createEmailExceedingLength(static::getTotalLimit())];
    }

    abstract protected static function getTotalLimit(): int;
}
