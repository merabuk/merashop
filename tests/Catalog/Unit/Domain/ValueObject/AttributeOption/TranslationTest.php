<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class TranslationTest extends BaseUnitTest
{
    public function testItCreatesValidTranslation(): void
    {
        $locale = LocaleEnum::En->value;
        $value = 'Red';

        $translation = new Translation($locale, $value);

        self::assertSame($locale, $translation->locale->value());
        self::assertSame($value, $translation->value);
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        new Translation('invalid', 'Red');
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidName(string $invalidValue): void
    {
        $this->expectException(InvalidAttributeOptionValueException::class);
        new Translation(LocaleEnum::En->value, $invalidValue);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', Translation::NAME_MAX_LENGTH + 1)];
    }
}
