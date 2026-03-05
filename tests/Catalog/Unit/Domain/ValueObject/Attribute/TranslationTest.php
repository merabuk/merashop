<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationTest extends TestCase
{
    public function testItCreatesValidTranslation(): void
    {
        $locale = LocaleEnum::En->value;
        $name = 'Color';

        $translation = new Translation($locale, $name);

        self::assertSame($locale, $translation->locale->value());
        self::assertSame($name, $translation->name);
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        new Translation('invalid', 'Color');
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidName(string $invalidValue): void
    {
        $this->expectException(InvalidAttributeNameException::class);
        new Translation(LocaleEnum::En->value, $invalidValue);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Translation::NAME_MAX_LENGTH + 1)];
    }
}
