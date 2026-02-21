<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service;

use App\Shared\Domain\Exception\EmailAddressFormatException;
use App\Shared\Domain\Exception\EmailAddressMaxLengthException;
use App\Shared\Domain\Service\EmailValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailValidatorTest extends TestCase
{
    #[DataProvider('validEmailProvider')]
    public function testItValidatesCorrectEmails(string $email, int $max, string $expected): void
    {
        self::assertSame($expected, EmailValidator::validate(email: $email, maxLength: $max));
    }

    public static function validEmailProvider(): iterable
    {
        yield 'simple email' => ['test@example.com', 50, 'test@example.com'];
        yield 'email with spaces' => ['  space@example.com  ', 50, 'space@example.com'];
        yield 'complex email' => ['very.common_address@sub.domain.com', 100, 'very.common_address@sub.domain.com'];
    }

    public function testThrowsExceptionWhenTooLong(): void
    {
        $this->expectException(EmailAddressMaxLengthException::class);
        EmailValidator::validate(email: 'long@example.com', maxLength: 5);
    }

    #[DataProvider('invalidEmailFormatProvider')]
    public function testThrowsExceptionOnInvalidFormat(string $invalidEmail): void
    {
        $this->expectException(EmailAddressFormatException::class);
        EmailValidator::validate(email: $invalidEmail, maxLength: 50);
    }

    public static function invalidEmailFormatProvider(): iterable
    {
        yield 'no at' => ['testexample.com'];
        yield 'no domain' => ['test@'];
        yield 'multiple at' => ['test@@example.com'];
        yield 'invalid characters' => ['test(parenthesis)@example.com'];
    }
}
