<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\ValueObject\Translation\InvalidTranslationNameException;

final class InvalidNameException extends InvalidCatalogValueObjectException
{
    public static function fromBaseException(InvalidTranslationNameException $baseException): self
    {
        return new self($baseException->getMessage(), previous: $baseException);
    }
}
