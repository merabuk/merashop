<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountEmailException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmailAddressTest extends TestCase
{
    /**
     * @throws InvalidAdminAccountEmailException
     */
    public function testItCreatesValidEmailAddress(): void
    {
        $email = 'test@example.com';
        $vo = EmailAddress::fromString($email);

        self::assertSame($email, $vo->value());
        self::assertSame($email, (string) $vo);
    }

    /**
     * @throws InvalidAdminAccountEmailException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = EmailAddress::fromString('test@example.com');
        $vo2 = EmailAddress::fromString('test@example.com');
        $vo3 = EmailAddress::fromString('different@example.com');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidEmailProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountEmailException::class);
        EmailAddress::fromString($invalidValue);
    }

    public static function invalidEmailProvider(): iterable
    {
        yield 'wrong format' => ['testexample.com'];
    }
}
