<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfilePhoneNumberException;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Tests\Shared\Unit\Domain\ValueObject\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validPhoneNumbersProvider')]
    public function testItCreatesValidPhoneNumber(string $phone, string $expected): void
    {
        $vo = PhoneNumber::fromString($phone);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validPhoneNumbersProvider(): iterable
    {
        yield 'only plus and digits' => ['+380998877666', '+380998877666'];
        yield 'with spaces' => ['+38 (099) 887-76-66', '+380998877666'];
        yield 'with dashes' => ['+38-099-887-76-66', '+380998877666'];
        yield 'formatted' => ['+38 (099) 887-76-66', '+380998877666'];
        yield 'expect trims' => [' +38 (099) 887-76-66 ', '+380998877666'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: PhoneNumber::class,
            value: '+380998877666',
            anotherValue: '+380995544333'
        );
    }

    #[DataProvider('invalidPhoneNumbersProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidCustomerProfilePhoneNumberException::class);
        PhoneNumber::fromString('invalid phone number');
    }

    public static function invalidPhoneNumbersProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => ['+380'.str_repeat('9', PhoneNumber::MAX_LENGTH - 4 + 1)];
    }
}
