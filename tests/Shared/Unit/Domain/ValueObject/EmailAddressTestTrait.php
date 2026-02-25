<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Tests\Shared\Support\Traits\EmailTestDataTrait;
use PHPUnit\Framework\Assert;

trait EmailAddressTestTrait
{
    use EmailTestDataTrait;

    protected function assertValidEmailAddress(string $className): void
    {
        $email = 'test@example.com';

        $this->assertHasStaticMethod($className);

        $vo = $className::fromString($email);

        Assert::assertSame($email, $vo->value());
        Assert::assertSame($email, (string) $vo);
    }

    protected function assertEmailAddressTrimming(string $className): void
    {
        $email = '  test@example.com  ';
        $expected = 'test@example.com';

        $this->assertHasStaticMethod($className);

        $vo = $className::fromString($email);
        Assert::assertSame($expected, $vo->value(), sprintf('VO %s must trim spaces', $className));
    }

    protected function assertEmailAddressEquality(string $className): void
    {
        $this->assertHasStaticMethod($className);

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

    private function assertHasStaticMethod(string $className): void
    {
        Assert::assertTrue(
            condition: method_exists(static::class, 'fromString'),
            message: sprintf('%s class must implement fromString method', $className)
        );
    }
}
