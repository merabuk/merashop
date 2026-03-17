<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryDescriptionException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\TranslationsValueObjectTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    use TranslationsValueObjectTrait;

    #[DataProvider('validTranslationsProvider')]
    public function testItCreatesValidCollection(
        array $data,
        int $expectedCount,
        string $expectedLocale,
        string $expectedName,
        string $expectedDescription,
    ): void {
        $vo = Translations::fromArray($data);

        self::assertCount($expectedCount, $vo);
        $translationByLocale = $vo->get($expectedLocale);
        self::assertSame($expectedName, $translationByLocale->name);
        self::assertSame($expectedDescription, $translationByLocale->description);
    }

    public static function validTranslationsProvider(): iterable
    {
        $data = self::getValidTranslations();

        yield 'valid' => [
            'data' => $data,
            'expectedCount' => 2,
            'expectedLocale' => 'uk',
            'expectedName' => $data['uk']['name'],
            'expectedDescription' => $data['uk']['description'],
        ];
        yield 'trimmed' => [
            'data' => ['en' => ['name' => '  Electronics  ', 'description' => '  All electronic devices  ']],
            'expectedCount' => 1,
            'expectedLocale' => 'en',
            'expectedName' => 'Electronics',
            'expectedDescription' => 'All electronic devices',
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
        $this->assertThrowsExceptionOnInvalidLocale(className: Translations::class);
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidNameInput(array $invalidValue): void
    {
        $this->expectException(InvalidCategoryNameException::class);
        Translations::fromArray($invalidValue);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'missing key' => [['en' => []]];
        yield 'empty' => [['en' => ['name' => '']]];
        yield 'only spaces' => [['en' => ['name' => '   ']]];
        yield 'too long' => [['en' => ['name' => str_repeat('a', Translation::NAME_MAX_LENGTH + 1)]]];
    }

    #[DataProvider('invalidDescriptionProvider')]
    public function testThrowsExceptionOnInvalidDescriptionInput(array $invalidValue): void
    {
        $this->expectException(InvalidCategoryDescriptionException::class);
        Translations::fromArray($invalidValue);
    }

    public static function invalidDescriptionProvider(): iterable
    {
        yield 'only spaces' => [['en' => ['name' => 'Test', 'description' => '    ']]];
        yield 'too long' => [['en' => ['name' => 'Test', 'description' => str_repeat('a', Translation::DESCRIPTION_MAX_LENGTH + 1)]]];
    }

    private static function getValidTranslations(): array
    {
        return [
            'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
            'uk' => ['name' => 'Електроніка', 'description' => 'Всі електронні пристрої'],
        ];
    }
}
