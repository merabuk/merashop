<?php

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class CategoryNotFoundException extends CatalogDomainException implements NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CategoryNotFound->value;
    }
}
