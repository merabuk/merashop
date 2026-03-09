<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\TemporaryImage;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidTemporaryImageUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid temporary image ULID');
    }
}
