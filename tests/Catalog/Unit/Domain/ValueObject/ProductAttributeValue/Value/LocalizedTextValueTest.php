<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedTextValueException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedTextValue;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class LocalizedTextValueTest extends BaseUnitTest
{
    #[DataProvider('validValuesProvider')]
    public function testItCreatesValidLocalizedStringValue(
        array $values,
        int $expectedCount,
        string $expectedLocale,
        string $expectedValue,
    ): void {
        $vo = new LocalizedTextValue($values);

        self::assertCount($expectedCount, $vo->toArray());
        $value = $vo->get($expectedLocale);
        self::assertNotNull($value);
        self::assertSame($expectedValue, $value);
    }

    public static function validValuesProvider(): iterable
    {
        $values = self::getValidLocalizedValues();

        yield 'valid' => [
            'values' => $values,
            'expectedCount' => 2,
            'expectedLocale' => 'uk',
            'expectedValue' => $values['uk'],
        ];
        yield 'trimmed' => [
            'values' => ['en' => '  Test  string  value  '],
            'expectedCount' => 1,
            'expectedLocale' => 'en',
            'expectedValue' => 'Test string value',
        ];
    }

    public function testItReturnsNullForMissingLocale(): void
    {
        $values = ['en' => self::getValidLocalizedValues()['en']];

        $vo = new LocalizedTextValue($values);

        self::assertNull($vo->get('uk'));
    }

    public function testItProvidesEqualityCheck(): void
    {
        $values = self::getValidLocalizedValues();

        $vo1 = new LocalizedTextValue($values);
        $vo2 = new LocalizedTextValue(array_reverse($values));
        $vo3 = new LocalizedTextValue(array_slice($values, 0, 1, true));

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidValueProvider')]
    public function testThrowsExceptionOnInvalidNameInput(array $invalidValue): void
    {
        $this->expectException(InvalidProductAttributeLocalizedTextValueException::class);

        LocalizedTextValue::fromArray($invalidValue);
    }

    public static function invalidValueProvider(): iterable
    {
        $values = self::getValidLocalizedValues();

        yield 'missing locale' => [
            'invalidValue' => ['en' => $values['en']],
        ];
        yield 'empty en value' => [
            'invalidValue' => [...$values, 'en' => ''],
        ];
        yield 'en only spaces' => [
            'invalidValue' => [...$values, 'en' => '   ']];
        yield 'en too long' => [
            'invalidValue' => [...$values, 'en' => str_repeat('a', LocalizedTextValue::MAX_LENGTH + 1)],
        ];
    }

    protected static function getValidLocalizedValues(): array
    {
        return [
            'en' => 'Test text value',
            'uk' => 'Тестове текстове значення',
        ];
    }
}
