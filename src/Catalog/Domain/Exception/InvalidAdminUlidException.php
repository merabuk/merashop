<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

final class InvalidAdminUlidException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsNotAValidUlid(): self
    {
        return new self('Invalid admin ULID');
    }
}
