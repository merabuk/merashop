<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeBaseLocalizedStringValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedTextValueException;

final readonly class LocalizedTextValue extends BaseLocalizedStringValue
{
    public const int MAX_LENGTH = 65_535;

    /**
     * @param array<string, string> $data [locale => value]
     *
     * @throws InvalidProductAttributeLocalizedTextValueException
     */
    public static function fromArray(array $data): self
    {
        try {
            return new self(self::mapAndEnsureIsValidValue($data));
        } catch (InvalidProductAttributeBaseLocalizedStringValueException $e) {
            throw InvalidProductAttributeLocalizedTextValueException::fromBase($e);
        }
    }
}
