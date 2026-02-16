<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\ConflictExceptionInterface;

abstract class CatalogConflictException extends CatalogDomainException implements ConflictExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'CATALOG_CONFLICT_ERROR';
    }
}
