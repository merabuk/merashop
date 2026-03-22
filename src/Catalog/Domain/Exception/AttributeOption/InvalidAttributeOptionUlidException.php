<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeOptionUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid attribute option ULID');
    }
}
