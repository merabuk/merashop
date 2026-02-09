<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Shared\Domain\Exception\InvalidStringException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\StringValidator;
use App\Shared\Domain\ValueObject\Locale;

final class Translation
{
    public const int NAME_MAX_LENGTH = 255;

    public readonly Locale $locale;
    public readonly string $name;

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public function __construct(string $locale, string $name) {
        $this->locale = Locale::fromString($locale);
        try {
            $this->name = StringValidator::validate($name, self::NAME_MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidAttributeNameException::fromBaseException($e);
        }
    }
}
