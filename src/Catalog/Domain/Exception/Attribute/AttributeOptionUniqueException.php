<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class AttributeOptionUniqueException extends InvalidCatalogValueObjectException
{
    public static function becauseDuplicateOption(string $ulid): self
    {
        return new self(message: sprintf('Attribute option with ulid "%s" already exists', $ulid));
    }

    public function getErrorCode(): string
    {
        return 'ATTRIBUTE_OPTION_UNIQUE_EXCEPTION';
    }
}
