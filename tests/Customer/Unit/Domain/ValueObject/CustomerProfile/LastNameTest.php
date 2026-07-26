<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileLastNameException;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class LastNameTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validLastNameProvider')]
    public function testItCreatesValidLastName(string $lastName, string $expected): void
    {
        $vo = LastName::fromString($lastName);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validLastNameProvider(): iterable
    {
        yield 'simple' => ['Dou', 'Dou'];
        yield 'with spaces' => ['Dou  Smith', 'Dou Smith'];
        yield 'trimmed' => ['  Dou  ', 'Dou'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: LastName::class,
            value: 'Dou',
            anotherValue: 'Smith'
        );
    }

    #[DataProvider('invalidLastNameProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidCustomerProfileLastNameException::class);
        LastName::fromString($invalidValue);
    }

    public static function invalidLastNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', LastName::MAX_LENGTH + 1)];
    }
}
