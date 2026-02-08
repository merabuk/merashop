<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\LogicException;

abstract class CatalogDomainException extends LogicException implements CatalogExceptionInterface
{
    public function getErrorCode(): string
    {
        return 'CATALOG_DOMAIN_ERROR';
    }
}
