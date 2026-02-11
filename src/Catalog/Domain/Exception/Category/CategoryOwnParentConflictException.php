<?php

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\ConflictExceptionInterface;

class CategoryOwnParentConflictException extends CatalogDomainException implements ConflictExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CategoryOwnParentConflict->value;
    }
}
