<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use Throwable;

final class InvalidProductAttributeDateValueException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Value is empty');
    }

    public static function becauseItIsDateMalformedString(?Throwable $previous = null): self
    {
        return new self(message: 'Value is not a valid date string', previous: $previous);
    }
}
