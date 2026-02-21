<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use PHPUnit\Framework\TestCase;

final class TranslationsTest extends TestCase
{
    /**
     * @throws InvalidLocaleException
     * @throws InvalidAttributeNameException
     */
    public function testItCreatesValidCollection(): void
    {
        $data = ['en' => ['name' => ' Color ']];
        $vo = Translations::fromArray($data);

        self::assertCount(1, $vo);
        self::assertSame('Color', $vo->get('en')?->name);
    }

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public function testItReturnsNullForMissingLocale(): void
    {
        $vo = Translations::fromArray(['en' => ['name' => 'Name']]);
        self::assertNull($vo->get('uk'));
    }

    /**
     * @throws InvalidLocaleException
     * @throws InvalidAttributeNameException
     */
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

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = Translations::fromArray(['en' => ['name' => 'Name']]);
        $vo2 = Translations::fromArray(['en' => ['name' => 'Name']]);
        $vo3 = Translations::fromArray(['en' => ['name' => 'Different']]);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
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

    /**
     * @throws InvalidAttributeNameException
     */
    public function testThrowsExceptionOnInvalidLocale(): void
    {
        $this->expectException(InvalidLocaleException::class);
        Translations::fromArray(['invalid' => ['name' => 'Test']]);
    }

    /**
     * @throws InvalidLocaleException
     */
    public function testThrowsExceptionOnMissingNameKey(): void
    {
        $this->expectException(InvalidAttributeNameException::class);
        Translations::fromArray(['en' => ['wrong_key' => 'Test']]);
    }

    /**
     * @throws InvalidLocaleException
     */
    public function testThrowsExceptionOnTooLongName(): void
    {
        $this->expectException(InvalidAttributeNameException::class);
        Translations::fromArray(['en' => ['name' => str_repeat('a', Translation::NAME_MAX_LENGTH + 1)]]);
    }
}
