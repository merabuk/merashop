<?php

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Shared\Domain\ValueObject\Temporal\DateValueObject;
use DateMalformedStringException;
use DateTimeImmutable;

final readonly class DateValue extends DateValueObject implements AttributeValueInterface
{
    /**
     * @throws InvalidProductAttributeDateValueException
     */
    public static function fromString(string $value): self
    {
        if ('' === $value) {
            throw InvalidProductAttributeDateValueException::becauseItIsEmpty();
        }
        try {
            return new self(new DateTimeImmutable($value));
        } catch (DateMalformedStringException $e) {
            throw InvalidProductAttributeDateValueException::becauseItIsDateMalformedString($e);
        }
    }

    public static function fromDateTime(DateTimeImmutable $data): self
    {
        return new self($data);
    }
}
