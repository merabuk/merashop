<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service;

use App\Shared\Domain\Exception\PhoneNumberFormatException;
use App\Shared\Domain\Exception\PhoneNumberMaxLengthException;
use App\Shared\Domain\Service\PhoneNumberValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhoneNumberValidatorTest extends TestCase
{
    #[DataProvider('validPhoneProvider')]
    public function testItValidatesAndFormatsPhone(string $input, string $expected): void
    {
        self::assertSame($expected, PhoneNumberValidator::validate(phoneNumber: $input, maxLength: 13));
    }

    public static function validPhoneProvider(): iterable
    {
        yield 'standard' => ['+380931234567', '+380931234567'];
        yield 'with dashes' => ['38-093-123-45-67', '+380931234567'];
        yield 'with spaces' => ['+38 093 123 45 67', '+380931234567'];
        yield 'no plus' => ['380931234567', '+380931234567'];
    }

    public function testThrowsExceptionWhenFormatIsInvalid(): void
    {
        $this->expectException(PhoneNumberFormatException::class);
        PhoneNumberValidator::validate(phoneNumber: '+15551234567', maxLength: 15);
    }

    public function testThrowsExceptionWhenTooLong(): void
    {
        $this->expectException(PhoneNumberMaxLengthException::class);
        PhoneNumberValidator::validate(phoneNumber: '380931234567890', maxLength: 10);
    }
}
