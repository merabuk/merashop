<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class SubjectTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validFromNameProvider')]
    public function testItCreatesValidFromName(string $name, string $expected): void
    {
        $vo = Subject::fromString($name);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validFromNameProvider(): iterable
    {
        yield 'valid name' => ['Welcome to our website!', 'Welcome to our website!'];
        yield 'trimmed' => ['  Welcome to our website!  ', 'Welcome to our website!'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Subject::class,
            value: 'Welcome to our website!',
            anotherValue: 'Welcome to our website.'
        );
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
