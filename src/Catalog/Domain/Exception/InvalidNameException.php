<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

final class InvalidNameException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Name cannot be empty');
    }

    public static function becauseItIsTooLong(int $maxLength): self
    {
        return new self(sprintf('Name is too long (max %d characters)', $maxLength));
    }
}
