<?php

namespace App\Catalog\Domain\ValueObject\ProductAttribute\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateMalformedStringException;
use DateTimeImmutable;

final readonly class DateValue extends DateTimeValueObject implements AttributeValueInterface
{
    /**
     * @throws InvalidProductAttributeDateValueException
     */
    public static function fromString(string $value): self
    {
        try {
            return new self(new DateTimeImmutable($value));
        } catch (DateMalformedStringException $e) {
            throw InvalidProductAttributeDateValueException::becauseItIsDateMalformedString($e);
        }
    }
}
