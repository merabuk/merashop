<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidCollection(): void
    {
        $data = ['en' => ['name' => ' Electronics ', 'description' => '  All  electronic  devices ']];
        $vo = Translations::fromArray($data);

        self::assertCount(1, $vo);
        self::assertSame('Electronics', $vo->get('en')?->name);
        self::assertSame('All electronic devices', $vo->get('en')?->description);
    }

    public function testItReturnsNullForMissingLocale(): void
    {
        $vo = Translations::fromArray(['en' => ['name' => 'Name', 'description' => 'Description']]);
        self::assertNull($vo->get('uk'));
    }

    public function testItCanBeIterated(): void
    {
        $data = [
            'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
            'uk' => ['name' => 'Електроніка', 'description' => 'Усі електронні пристрої'],
        ];
        $vo = Translations::fromArray($data);

        $iterated = [];
        foreach ($vo as $locale => $translation) {
            self::assertInstanceOf(Translation::class, $translation);
            $iterated[$locale] = $translation->name;
        }

        self::assertCount(2, $iterated);
        self::assertSame('Electronics', $iterated['en']);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertArrayVOProvidesEqualityCheck(
            className: Translations::class,
            value: [
                'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
                'uk' => ['name' => 'Електроніка', 'description' => 'Усі електронні пристрої'],
            ],
            shuffledValue: [
                'uk' => ['name' => 'Електроніка', 'description' => 'Усі електронні пристрої'],
                'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
            ],
            anotherValue: ['en' => ['name' => 'Home', 'description' => 'All for home']]
        );
    }

    public function testItSortsKeysForDeterministicStringRepresentation(): void
    {
        $vo1 = Translations::fromArray([
            'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
            'uk' => ['name' => 'Електроніка', 'description' => 'Усі електронні пристрої'],
        ]);
        $vo2 = Translations::fromArray([
            'uk' => ['name' => 'Електроніка', 'description' => 'Усі електронні пристрої'],
            'en' => ['name' => 'Electronics', 'description' => 'All electronic devices'],
        ]);

        self::assertSame((string) $vo1, (string) $vo2);
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        Translations::fromArray(['invalid' => ['name' => 'Test', 'description' => null]]);
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue): void
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
}
