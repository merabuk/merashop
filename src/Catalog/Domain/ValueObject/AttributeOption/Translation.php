<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\TranslationInterface;
use App\Shared\Domain\ValueObject\Locale;

final readonly class Translation implements TranslationInterface
{
    public const int NAME_MAX_LENGTH = 255;

    public Locale $locale;
    public string $value;

    /**
     * @throws InvalidAttributeOptionValueException
     * @throws InvalidLocaleException
     */
    public function __construct(string $locale, string $value)
    {
        $this->locale = Locale::fromString($locale);
        try {
            $this->value = StringValidator::validate(rawValue: $value, maxLength: self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidAttributeOptionValueException::fromBaseException($e);
        }
    }

    /**
     * @return array{value: string}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}
