<?php

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class ProductNotFoundException extends CatalogDomainException implements NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductNotFound->value;
    }
}
