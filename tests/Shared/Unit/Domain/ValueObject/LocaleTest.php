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
    /**
     * @throws InvalidLocaleException
     */
    public function testItCreatesValidLocaleFromEnumCases(): void
    {
        foreach (LocaleEnum::cases() as $locale) {
            $vo = Locale::fromString($locale->value);
            self::assertSame($locale->value, $vo->value());
            self::assertSame($locale->value, (string) $vo);
        }
    }

    /**
     * @throws InvalidLocaleException
     */
    public function testItTrimsInput(): void
    {
        $locale = LocaleEnum::En->value;
        $vo = Locale::fromString('  '.$locale.'  ');
        self::assertSame($locale, $vo->value());
    }

    /**
     * @throws InvalidLocaleException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = Locale::fromString(LocaleEnum::En->value);
        $vo2 = Locale::fromString(LocaleEnum::En->value);
        $vo3 = Locale::fromString(LocaleEnum::Uk->value);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
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
