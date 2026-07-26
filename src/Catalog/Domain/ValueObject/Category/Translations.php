<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\AbstractTranslations;

/**
 * @extends AbstractTranslations<Translation>
 */
final readonly class Translations extends AbstractTranslations
{
    /**
     * @param array<string, array{name?: string, description: ?string}> $data
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public static function fromArray(array $data): self
    {
        $translations = [];
        foreach ($data as $locale => $item) {
            $name = $item['name'] ?? throw InvalidCategoryNameException::becauseItIsEmpty($locale);
            $description = $item['description'] ?? null;

            $translations[$locale] = new Translation(locale: $locale, name: $name, description: $description);
        }

        return new self($translations);
    }
}
