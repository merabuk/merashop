<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\TemporaryImage;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidTemporaryImageIdException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('Temporary image ID must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_TEMPORARY_IMAGE_ID';
    }
}
