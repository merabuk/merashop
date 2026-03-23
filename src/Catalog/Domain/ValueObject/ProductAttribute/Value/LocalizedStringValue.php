<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeBaseLocalizedStringValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedStringValueException;

final readonly class LocalizedStringValue extends BaseLocalizedStringValue
{
    public const int MAX_LENGTH = 255;

    /**
     * @param array<string, string> $data [locale => value]
     *
     * @throws InvalidProductAttributeLocalizedStringValueException
     */
    public static function fromArray(array $data): self
    {
        try {
            return new self(self::mapAndEnsureIsValidValue($data));
        } catch (InvalidProductAttributeBaseLocalizedStringValueException $e) {
            throw InvalidProductAttributeLocalizedStringValueException::fromBase($e);
        }
    }
}
