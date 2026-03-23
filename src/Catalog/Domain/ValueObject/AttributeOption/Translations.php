<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\AbstractTranslations;

/**
 * @extends AbstractTranslations<Translation>
 */
final readonly class Translations extends AbstractTranslations
{
    /**
     * @param array<string, array{value?: string}> $data
     *
     * @throws InvalidAttributeOptionValueException
     * @throws InvalidLocaleException
     */
    public static function fromArray(array $data): self
    {
        $translations = [];
        foreach ($data as $locale => $item) {
            $value = $item['value'] ?? throw InvalidAttributeOptionValueException::becauseItIsEmpty($locale);

            $translations[$locale] = new Translation(locale: $locale, value: $value);
        }

        return new self($translations);
    }
}
