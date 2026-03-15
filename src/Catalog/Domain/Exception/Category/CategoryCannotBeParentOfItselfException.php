<?php

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\ConflictExceptionInterface;

class CategoryCannotBeParentOfItselfException extends CatalogDomainException implements ClientFacingExceptionInterface, ConflictExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CategoryCannotBeParentOfItselfConflict->value;
    }
}
