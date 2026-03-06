<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryDescriptionException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationTest extends TestCase
{
    #[DataProvider('validTranslationProvider')]
    public function testItCreatesValidTranslation(string $locale, string $name, ?string $description): void
    {
        $translation = new Translation(locale: $locale, name: $name, description: $description);

        self::assertSame($locale, $translation->locale->value());
        self::assertSame($name, $translation->name);
        self::assertSame($description, $translation->description);
    }

    public static function validTranslationProvider(): iterable
    {
        yield 'simple' => [LocaleEnum::En->value, 'Electronics', 'All electronic devices'];
        yield 'with null description' => [LocaleEnum::En->value, 'Electronics', null];
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        new Translation(locale: 'invalid', name: 'Color');
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidName(string $invalidValue): void
    {
        $this->expectException(InvalidCategoryNameException::class);
        new Translation(locale: LocaleEnum::En->value, name: $invalidValue);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Translation::NAME_MAX_LENGTH + 1)];
    }

    #[DataProvider('invalidDescriptionProvider')]
    public function testThrowsExceptionOnInvalidDescription(string $invalidValue): void
    {
        $this->expectException(InvalidCategoryDescriptionException::class);
        new Translation(locale: LocaleEnum::En->value, name: 'Name', description: $invalidValue);
    }

    public static function invalidDescriptionProvider(): iterable
    {
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Translation::DESCRIPTION_MAX_LENGTH + 1)];
    }
}
