<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidCollection(): void
    {
        $data = ['en' => ['name' => ' Color ']];
        $vo = Translations::fromArray($data);

        self::assertCount(1, $vo);
        self::assertSame('Color', $vo->get('en')?->name);
    }

    public function testItReturnsNullForMissingLocale(): void
    {
        $vo = Translations::fromArray(['en' => ['name' => 'Name']]);
        self::assertNull($vo->get('uk'));
    }

    public function testItCanBeIterated(): void
    {
        $data = [
            'en' => ['name' => 'Color'],
            'uk' => ['name' => 'Колір'],
        ];
        $vo = Translations::fromArray($data);

        $iterated = [];
        foreach ($vo as $locale => $translation) {
            self::assertInstanceOf(Translation::class, $translation);
            $iterated[$locale] = $translation->name;
        }

        self::assertCount(2, $iterated);
        self::assertSame('Color', $iterated['en']);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertArrayVOProvidesEqualityCheck(
            className: Translations::class,
            value: ['en' => ['name' => 'Name'], 'uk' => ['name' => 'Назва']],
            shuffledValue: ['uk' => ['name' => 'Назва'], 'en' => ['name' => 'Name']],
            anotherValue: ['en' => ['name' => 'Different']]
        );
    }

    public function testItSortsKeysForDeterministicStringRepresentation(): void
    {
        $vo1 = Translations::fromArray([
            'en' => ['name' => 'Name'],
            'uk' => ['name' => 'Назва'],
        ]);
        $vo2 = Translations::fromArray([
            'uk' => ['name' => 'Назва'],
            'en' => ['name' => 'Name'],
        ]);

        self::assertSame((string) $vo1, (string) $vo2);
    }

    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        Translations::fromArray(['invalid' => ['name' => 'Test']]);
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue): void
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
}
