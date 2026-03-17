<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductDescriptionException;
use App\Catalog\Domain\Exception\Product\InvalidProductNameException;
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
     * @throws InvalidLocaleException
     * @throws InvalidProductDescriptionException
     * @throws InvalidProductNameException
     */
    public function __construct(
        string $locale,
        string $name,
        ?string $description = null,
    ) {
        $this->locale = Locale::fromString($locale);
        try {
            $this->name = StringValidator::validate($name, self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidProductNameException::fromBaseException($e);
        }
        try {
            $this->description = $description
                ? StringValidator::validate($description, self::DESCRIPTION_MAX_LENGTH)
                : null;
        } catch (InvalidStringException $e) {
            throw InvalidProductDescriptionException::fromBaseException($e);
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
