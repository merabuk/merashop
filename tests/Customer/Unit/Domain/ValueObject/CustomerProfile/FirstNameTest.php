<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileFirstNameException;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FirstNameTest extends TestCase
{
    public function testItCreatesValidFirstName(): void
    {
        $firstName = 'John';
        $vo = FirstName::fromString($firstName);

        self::assertSame($firstName, $vo->value());
        self::assertSame($firstName, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $firstName1 = 'John';
        $firstName2 = 'Jane';
        $vo1 = FirstName::fromString($firstName1);
        $vo2 = FirstName::fromString($firstName1);
        $vo3 = FirstName::fromString($firstName2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $firstName = 'John';
        $vo = FirstName::fromString('  '.$firstName.'  ');

        self::assertSame($firstName, $vo->value());
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
