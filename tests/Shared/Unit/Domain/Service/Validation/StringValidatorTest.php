<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\StringEmptyException;
use App\Shared\Domain\Exception\Services\StringMaxLengthException;
use App\Shared\Domain\Exception\Services\StringMinLengthException;
use App\Shared\Domain\Service\Validation\StringValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringValidatorTest extends TestCase
{
    #[DataProvider('validStringsProvider')]
    public function testItValidatesCorrectStrings(string $value, int $max, int $min, string $expected): void
    {
        $result = StringValidator::validate(rawValue: $value, maxLength: $max, minLength: $min);
        self::assertSame($expected, $result);
    }

    public static function validStringsProvider(): iterable
    {
        yield 'normal string' => ['Hello', 10, 2, 'Hello'];
        yield 'string with spaces' => ['  Trim Me  ', 10, 2, 'Trim Me'];
        yield 'normalize spaces' => ['Some  extra   spaces  between  words', 35, 2, 'Some extra spaces between words'];
        yield 'normalize break lines' => ["Some  \n\n\n\n  extra  \n\n\n\n  breaks", 35, 2, "Some\n\nextra\n\nbreaks"];
        yield 'multibyte string' => ['Привіт', 6, 6, 'Привіт'];
        yield 'exact max length' => ['ABCDE', 5, 0, 'ABCDE'];
        yield 'exact min length' => ['ABC', 10, 3, 'ABC'];
        yield 'emoji support' => ['🚀', 2, 1, '🚀'];
    }

    #[DataProvider('invalidStringsProvider')]
    public function testThrowsExceptionOnInvalidValue(
        string $invalidValue,
        int $maxLength,
        int $minLength,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        StringValidator::validate(rawValue: $invalidValue, maxLength: $maxLength, minLength: $minLength);
    }

    public static function invalidStringsProvider(): iterable
    {
        yield 'empty string' => ['', 10, 0, StringEmptyException::class];
        yield 'string with only spaces' => ['   ', 10, 0, StringEmptyException::class];
        yield 'too long string' => ['Too Long String', 5, 0, StringMaxLengthException::class];
        yield 'too short string' => ['Short', 10, 8, StringMinLengthException::class];
        yield 'multibyte long string' => ['Тест+', 4, 0, StringMaxLengthException::class];
    }
}
