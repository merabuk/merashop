<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\AbstractTranslations;

/**
 * @extends AbstractTranslations<Translation>
 */
final readonly class Translations extends AbstractTranslations
{
    /**
     * @param array<string, array{name?: string}> $data
     *
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public static function fromArray(array $data): self
    {
        $translations = [];
        foreach ($data as $locale => $item) {
            $name = $item['name'] ?? throw InvalidAttributeNameException::becauseItIsEmpty($locale);

            $translations[$locale] = new Translation(locale: $locale, name: $name);
        }

        return new self($translations);
    }
}
