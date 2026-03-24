<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\Services\Validation\InvalidUrlException;

final class InvalidProductAttributeUrlValueException extends InvalidCatalogValueObjectException
{
    public static function fromBaseException(InvalidUrlException $e): self
    {
        return new self($e->getMessage(), previous: $e);
    }
}
