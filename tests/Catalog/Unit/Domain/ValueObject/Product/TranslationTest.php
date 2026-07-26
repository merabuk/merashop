<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductDescriptionException;
use App\Catalog\Domain\Exception\Product\InvalidProductNameException;
use App\Catalog\Domain\ValueObject\Product\Translation;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class TranslationTest extends BaseUnitTest
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
        yield 'simple' => [
            'locale' => LocaleEnum::En->value,
            'name' => 'Cup',
            'description' => 'The best teacup'];
        yield 'with null description' => [
            'locale' => LocaleEnum::En->value,
            'name' => 'Mug',
            'description' => null,
        ];
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        new Translation(locale: 'invalid', name: 'Color');
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidName(string $invalidValue): void
    {
        $this->expectException(InvalidProductNameException::class);
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
        $this->expectException(InvalidProductDescriptionException::class);
        new Translation(locale: LocaleEnum::En->value, name: 'Name', description: $invalidValue);
    }

    public static function invalidDescriptionProvider(): iterable
    {
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Translation::DESCRIPTION_MAX_LENGTH + 1)];
    }
}
