<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailErrorMessageException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ErrorMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ErrorMessageTest extends TestCase
{
    public function testItCreatesValidErrorMessage(): void
    {
        $errorMessage = 'Error message';
        $vo = ErrorMessage::fromString($errorMessage);

        self::assertSame($errorMessage, $vo->value());
        self::assertSame($errorMessage, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $errorMessage1 = 'Error message!';
        $errorMessage2 = 'Error message.';

        $vo1 = ErrorMessage::fromString($errorMessage1);
        $vo2 = ErrorMessage::fromString($errorMessage1);
        $vo3 = ErrorMessage::fromString($errorMessage2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $errorMessage = 'Error message';
        $vo = ErrorMessage::fromString('  '.$errorMessage.'  ');

        self::assertSame($errorMessage, $vo->value());
    }

    #[DataProvider('invalidErrorMessageProvider')]
    public function testItThrowsExceptionOnInvalidInput(string $invalidValue): void
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
