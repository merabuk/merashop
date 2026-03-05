<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailFromNameException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\Tests\Shared\Unit\Domain\ValueObject\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FromNameTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validFromNameProvider')]
    public function testItCreatesValidFromName(string $name, string $expected): void
    {
        $vo = FromName::fromString($name);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validFromNameProvider(): iterable
    {
        yield 'valid' => ['John Doe', 'John Doe'];
        yield 'name with special characters' => ['John Doe & Johnson', 'John Doe & Johnson'];
        yield 'trimmed' => ['  John Doe  ', 'John Doe'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: FromName::class,
            value: 'John Doe',
            anotherValue: 'Jane Doe'
        );
    }

    #[DataProvider('invalidFromNameProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailFromNameException::class);
        FromName::fromString($invalidValue);
    }

    public static function invalidFromNameProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'string with only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', FromName::MAX_LENGTH + 1)];
    }
}
