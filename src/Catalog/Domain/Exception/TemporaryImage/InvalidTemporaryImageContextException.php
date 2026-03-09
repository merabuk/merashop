<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\TemporaryImage;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidTemporaryImageContextException extends InvalidCatalogValueObjectException
{
    /**
     * @param string[] $availableValues
     */
    public static function becauseItIsNotAValidContext(string $invalidValue, array $availableValues): self
    {
        return new self(sprintf(
            '"%s" is not a valid Temporary image context. Available context: %s',
            $invalidValue,
            implode(', ', $availableValues)
        ));
    }
}
