<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryDescriptionException;
use App\Catalog\Domain\Exception\Category\InvalidCategoryNameException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\Locale;

final class Translation
{
    public const int NAME_MAX_LENGTH = 255;
    public const int DESCRIPTION_MAX_LENGTH = 65_535;

    public readonly Locale $locale;
    public readonly string $name;
    public readonly ?string $description;

    /**
     * @throws InvalidCategoryDescriptionException
     * @throws InvalidCategoryNameException
     * @throws InvalidLocaleException
     */
    public function __construct(
        string $locale,
        string $name,
        ?string $description = null
    ) {
        $this->locale = Locale::fromString($locale);
        try {
            $this->name = StringValidator::validate($name, self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidCategoryNameException::fromBaseException($e);
        }
        try {
            $this->description = StringValidator::validate($description, self::DESCRIPTION_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidCategoryDescriptionException::fromBaseException($e);
        }
    }
}
