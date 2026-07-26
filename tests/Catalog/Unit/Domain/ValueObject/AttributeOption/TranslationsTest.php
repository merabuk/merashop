<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\TranslationsValueObjectTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class TranslationsTest extends BaseUnitTest
{
    use TranslationsValueObjectTrait;

    #[DataProvider('validTranslationsProvider')]
    public function testItCreatesValidCollection(
        array $data,
        int $expectedCount,
        string $expectedLocale,
        string $expectedValue,
    ): void {
        $vo = Translations::fromArray($data);

        self::assertCount($expectedCount, $vo);
        $translationByLocale = $vo->get($expectedLocale);
        self::assertNotNull($translationByLocale);
        self::assertSame($expectedValue, $translationByLocale->value);
    }

    public static function validTranslationsProvider(): iterable
    {
        $data = self::getValidTranslations();

        yield 'valid' => [
            'data' => $data,
            'expectedCount' => 2,
            'expectedLocale' => 'uk',
            'expectedValue' => $data['uk']['value'],
        ];
        yield 'trimmed' => [
            'data' => ['en' => ['value' => '  Red  ']],
            'expectedCount' => 1,
            'expectedLocale' => 'en',
            'expectedValue' => 'Red',
        ];
    }

    public function testItReturnsNullForMissingLocale(): void
    {
        $this->assertReturnsNullForMissingLocale(className: Translations::class);
    }

    public function testItCanBeIterated(): void
    {
        $this->assertCanBeIterated(className: Translations::class, childClassName: Translation::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertProvidesEqualityCheck(className: Translations::class);
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->assertThrowsExceptionOnInvalidLocale(className: Translations::class, translationKey: 'value');
    }

    #[DataProvider('invalidValueProvider')]
    public function testThrowsExceptionOnInvalidNameInput(array $invalidValue): void
    {
        $this->expectException(InvalidAttributeOptionValueException::class);
        Translations::fromArray($invalidValue);
    }

    public static function invalidValueProvider(): iterable
    {
        yield 'missing key' => [['en' => []]];
        yield 'empty' => [['en' => ['value' => '']]];
        yield 'only spaces' => [['en' => ['value' => '   ']]];
        yield 'too long' => [['en' => ['value' => str_repeat('a', Translation::NAME_MAX_LENGTH + 1)]]];
    }

    private static function getValidTranslations(): array
    {
        return [
            'en' => ['value' => 'Red'],
            'uk' => ['value' => 'Червоний'],
        ];
    }
}
