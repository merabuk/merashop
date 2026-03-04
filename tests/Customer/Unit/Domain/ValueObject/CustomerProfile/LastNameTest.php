<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileLastNameException;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LastNameTest extends TestCase
{
    public function testItCreatesValidLastName(): void
    {
        $lastName = 'Dou';
        $vo = LastName::fromString($lastName);

        self::assertSame($lastName, $vo->value());
        self::assertSame($lastName, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $lastName1 = 'Dou';
        $lastName2 = 'Smith';
        $vo1 = LastName::fromString($lastName1);
        $vo2 = LastName::fromString($lastName1);
        $vo3 = LastName::fromString($lastName2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $lastName = 'Dou';
        $vo = LastName::fromString('  '.$lastName.'  ');

        self::assertSame($lastName, $vo->value());
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
