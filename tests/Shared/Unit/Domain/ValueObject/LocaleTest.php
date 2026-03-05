<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\Locale;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LocaleTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validLocaleProvider')]
    public function testItCreatesValidLocaleFromEnumCases(string $locale, string $expected): void
    {
        $vo = Locale::fromString($locale);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validLocaleProvider(): iterable
    {
        foreach (LocaleEnum::cases() as $case) {
            yield $case->name => [$case->value, $case->value];
        }

        yield 'trimmed' => ['   en   ', 'en'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Locale::class,
            value: LocaleEnum::En->value,
            anotherValue: LocaleEnum::Uk->value
        );
    }

    #[DataProvider('invalidLocaleProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidLocaleException::class);
        Locale::fromString($invalidValue);
    }

    public static function invalidLocaleProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long string' => [str_repeat('a', Locale::MAX_LENGTH + 1)];
        yield 'not supported locale' => ['fr'];
        yield 'wrong case' => ['EN'];
    }
}
