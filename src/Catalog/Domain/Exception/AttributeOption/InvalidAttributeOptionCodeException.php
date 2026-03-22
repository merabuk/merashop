<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidAttributeOptionCodeException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Attribute option code cannot be empty');
    }

    public static function becauseItIsTooLong(): self
    {
        return new self('Attribute option code cannot be longer than 255 characters');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_ATTRIBUTE_OPTION_CODE';
    }
}
