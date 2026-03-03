<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SubjectTest extends TestCase
{
    public function testItCreatesValidFromName(): void
    {
        $name = 'Welcome to our website!';
        $vo = Subject::fromString($name);

        self::assertSame($name, $vo->value());
        self::assertSame($name, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $name1 = 'Welcome to our website!';
        $name2 = 'Welcome to our website.';

        $vo1 = Subject::fromString($name1);
        $vo2 = Subject::fromString($name1);
        $vo3 = Subject::fromString($name2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $name = 'Welcome to our website!';
        $vo = Subject::fromString('  '.$name.'  ');

        self::assertSame($name, $vo->value());
    }

    #[DataProvider('invalidFromNameProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailSubjectException::class);
        Subject::fromString($invalidValue);
    }

    public static function invalidFromNameProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'string with only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Subject::MAX_LENGTH + 1)];
    }
}
