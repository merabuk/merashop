<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
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
        string $expectedName,
    ): void {
        $vo = Translations::fromArray($data);

        self::assertCount($expectedCount, $vo);
        $translationByLocale = $vo->get($expectedLocale);
        self::assertNotNull($translationByLocale);
        self::assertSame($expectedName, $translationByLocale->name);
    }

    public static function validTranslationsProvider(): iterable
    {
        $data = self::getValidTranslations();

        yield 'valid' => [
            'data' => $data,
            'expectedCount' => 2,
            'expectedLocale' => 'uk',
            'expectedName' => $data['uk']['name'],
        ];
        yield 'trimmed' => [
            'data' => ['en' => ['name' => '  Color  ']],
            'expectedCount' => 1,
            'expectedLocale' => 'en',
            'expectedName' => 'Color',
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
        $this->expectException(InvalidAttributeNameException::class);
        Translations::fromArray($invalidValue);
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'missing key' => [['en' => []]];
        yield 'empty' => [['en' => ['name' => '']]];
        yield 'only spaces' => [['en' => ['name' => '   ']]];
        yield 'too long' => [['en' => ['name' => str_repeat('a', Translation::NAME_MAX_LENGTH + 1)]]];
    }

    private static function getValidTranslations(): array
    {
        return [
            'en' => ['name' => 'Color'],
            'uk' => ['name' => 'Колір'],
        ];
    }
}
