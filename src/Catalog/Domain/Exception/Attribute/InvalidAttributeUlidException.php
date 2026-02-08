<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid attribute ULID');
    }
}
