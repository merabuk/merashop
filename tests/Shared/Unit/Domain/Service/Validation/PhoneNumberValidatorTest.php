<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Validation\PhoneNumberFormatException;
use App\Shared\Domain\Exception\Services\Validation\PhoneNumberMaxLengthException;
use App\Shared\Domain\Service\Validation\PhoneNumberValidator;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class PhoneNumberValidatorTest extends BaseUnitTest
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
