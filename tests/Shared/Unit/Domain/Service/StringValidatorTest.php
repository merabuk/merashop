<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service;

use App\Shared\Domain\Exception\StringEmptyException;
use App\Shared\Domain\Exception\StringMaxLengthException;
use App\Shared\Domain\Exception\StringMinLengthException;
use App\Shared\Domain\Service\StringValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringValidatorTest extends TestCase
{
    #[DataProvider('validStringsProvider')]
    public function testItValidatesCorrectStrings(string $value, int $max, int $min, string $expected): void
    {
        $result = StringValidator::validate(value: $value, maxLength: $max, minLength: $min);
        self::assertSame($expected, $result);
    }

    public static function validStringsProvider(): iterable
    {
        yield 'normal string' => ['Hello', 10, 2, 'Hello'];
        yield 'string with spaces' => ['  Trim Me  ', 10, 2, 'Trim Me'];
        yield 'multibyte string' => ['Привіт', 10, 2, 'Привіт'];
        yield 'exact max length' => ['ABCDE', 5, 0, 'ABCDE'];
        yield 'exact min length' => ['ABC', 10, 3, 'ABC'];
        yield 'emoji support' => ['🚀', 2, 1, '🚀'];
    }

    public function testItThrowsExceptionWhenEmptyAfterTrim(): void
    {
        $this->expectException(StringEmptyException::class);
        StringValidator::validate(value: '   ', maxLength: 10, minLength: 0);
    }

    public function testItThrowsExceptionWhenTooLong(): void
    {
        $this->expectException(StringMaxLengthException::class);
        StringValidator::validate(value: 'Too Long String', maxLength: 5, minLength: 0);
    }

    public function testItThrowsExceptionWhenTooShort(): void
    {
        $this->expectException(StringMinLengthException::class);
        StringValidator::validate(value: 'Short', maxLength: 10, minLength: 8);
    }

    public function testItHandlesMultibyteLengthCorrectly(): void
    {
        $result = StringValidator::validate(value: 'Тест', maxLength: 4, minLength: 4);
        self::assertSame('Тест', $result);

        $this->expectException(StringMaxLengthException::class);
        StringValidator::validate(value: 'Тест+', maxLength: 4, minLength: 0);
    }
}
