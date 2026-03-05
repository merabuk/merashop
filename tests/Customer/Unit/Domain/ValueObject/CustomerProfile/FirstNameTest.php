<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileFirstNameException;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FirstNameTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validFirstNameProvider')]
    public function testItCreatesValidFirstName(string $firstName, string $expected): void
    {
        $vo = FirstName::fromString($firstName);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validFirstNameProvider(): iterable
    {
        yield 'simple' => ['John', 'John'];
        yield 'trimmed' => ['  John  ', 'John'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: FirstName::class,
            value: 'John',
            anotherValue: 'Jane'
        );
    }

    #[DataProvider('invalidFirstNameProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidCustomerProfileFirstNameException::class);
        FirstName::fromString($invalidValue);
    }

    public static function invalidFirstNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', FirstName::MAX_LENGTH + 1)];
    }
}
