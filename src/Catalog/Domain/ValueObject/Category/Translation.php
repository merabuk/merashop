<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryDescriptionException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\TranslationInterface;
use App\Shared\Domain\ValueObject\Locale;

final readonly class Translation implements TranslationInterface
{
    public const int NAME_MAX_LENGTH = 255;
    public const int DESCRIPTION_MAX_LENGTH = 65_535;

    public Locale $locale;
    public string $name;
    public ?string $description;

    /**
     * @throws InvalidCategoryDescriptionException
     * @throws InvalidCategoryNameException
     * @throws InvalidLocaleException
     */
    public function __construct(
        string $locale,
        string $name,
        ?string $description = null,
    ) {
        $this->locale = Locale::fromString($locale);
        try {
            $this->name = StringValidator::validate(rawValue: $name, maxLength: self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidCategoryNameException::fromBaseException($e);
        }
        try {
            $this->description = $description
                ? StringValidator::validate(rawValue: $description, maxLength: self::DESCRIPTION_MAX_LENGTH)
                : null;
        } catch (InvalidStringException $e) {
            throw InvalidCategoryDescriptionException::fromBaseException($e);
        }
    }

    /**
     * @return array{name: string, description: ?string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
