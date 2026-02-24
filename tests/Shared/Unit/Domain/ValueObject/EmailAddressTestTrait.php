<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Tests\Shared\Support\Traits\EmailTestDataTrait;
use PHPUnit\Framework\Assert;
use RuntimeException;

trait EmailAddressTestTrait
{
    use EmailTestDataTrait;

    protected function assertValidEmailAddress(string $className): void
    {
        $email = 'test@example.com';

        if (!method_exists($className, 'fromString')) {
            throw new RuntimeException(sprintf('%s class must implement fromString method', $className));
        }

        $vo = $className::fromString($email);

        Assert::assertSame($email, $vo->value());
        Assert::assertSame($email, (string) $vo);
    }

    protected function assertEmailAddressEquality(string $className): void
    {
        if (!method_exists($className, 'fromString')) {
            throw new RuntimeException(sprintf('%s class must implement fromString method', $className));
        }

        $vo1 = $className::fromString('test@example.com');
        $vo2 = $className::fromString('test@example.com');
        $vo3 = $className::fromString('different@example.com');

        Assert::assertTrue($vo1->equals($vo2));
        Assert::assertFalse($vo1->equals($vo3));
    }

    public static function invalidEmailSampleProvider(): iterable
    {
        yield 'wrong format' => ['not-an-email'];
        yield 'local part too long' => [self::createEmailWithLongLocalPart()];
        yield 'total length exceeded' => [self::createEmailExceedingLength(static::getTotalLimit())];
    }

    abstract protected static function getTotalLimit(): int;
}
