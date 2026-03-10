<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailDriverException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DriverTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('driverEnumProvider')]
    public function testItCreatesValidDriverFromEnum(DriverEnum $enum): void
    {
        $vo = Driver::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('driverEnumProvider')]
    public function testItCreatesValidDriverFromString(DriverEnum $enum): void
    {
        $vo = Driver::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function driverEnumProvider(): iterable
    {
        foreach (DriverEnum::cases() as $case) {
            yield $case->value => [$case];
        }
    }

    public function testItTrimsInput(): void
    {
        $driver = DriverEnum::Log;
        $vo = Driver::fromString('  '.$driver->value.'  ');

        self::assertSame($driver, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Driver::class,
            enum: DriverEnum::Log,
            anotherEnum: DriverEnum::Smtp,
        );
    }

    #[DataProvider('invalidDriverProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailDriverException::class);
        Driver::fromString($invalidValue);
    }

    public static function invalidDriverProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['LOG'];
        yield 'random string' => ['not-a-driver'];
    }
}
