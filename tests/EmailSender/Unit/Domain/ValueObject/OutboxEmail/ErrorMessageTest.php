<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ErrorMessage;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class ErrorMessageTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validErrorMessageProvider')]
    public function testItCreatesValidErrorMessage(string $errorMessage, string $expected): void
    {
        $vo = ErrorMessage::fromString($errorMessage);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validErrorMessageProvider(): iterable
    {
        yield 'valid' => ['Error message!', 'Error message!'];
        yield 'trimmed' => ['  Error message!  ', 'Error message!'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: ErrorMessage::class,
            value: 'Error message!',
            anotherValue: 'Error message.'
        );
    }

    #[DataProvider('invalidErrorMessageProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailErrorMessageException::class);
        ErrorMessage::fromString($invalidValue);
    }

    public static function invalidErrorMessageProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
    }
}
