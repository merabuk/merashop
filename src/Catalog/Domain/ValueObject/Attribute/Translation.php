<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\TranslationInterface;
use App\Shared\Domain\ValueObject\Locale;

final readonly class Translation implements TranslationInterface
{
    public const int NAME_MAX_LENGTH = 255;

    public Locale $locale;
    public string $name;

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public function __construct(string $locale, string $name)
    {
        $this->locale = Locale::fromString($locale);
        try {
            $this->name = StringValidator::validate(rawValue: $name, maxLength: self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidAttributeNameException::fromBaseException($e);
        }
    }

    /**
     * @return array{name: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
