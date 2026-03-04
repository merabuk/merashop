<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailFromNameException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FromNameTest extends TestCase
{
    public function testItCreatesValidFromName(): void
    {
        $name = 'John Doe';
        $vo = FromName::fromString($name);

        self::assertSame($name, $vo->value());
        self::assertSame($name, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $name1 = 'John Doe';
        $name2 = 'Jane Doe';

        $vo1 = FromName::fromString($name1);
        $vo2 = FromName::fromString($name1);
        $vo3 = FromName::fromString($name2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $name = 'John Doe';
        $vo = FromName::fromString('  '.$name.'  ');

        self::assertSame($name, $vo->value());
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
