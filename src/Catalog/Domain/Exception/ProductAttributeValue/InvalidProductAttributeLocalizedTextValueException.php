<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductAttributeLocalizedTextValueException extends InvalidCatalogValueObjectException
{
    public static function fromBase(InvalidProductAttributeBaseLocalizedStringValueException $baseException): self
    {
        return new self('Localized text value exception: '.$baseException->getMessage(), previous: $baseException);
    }
}
