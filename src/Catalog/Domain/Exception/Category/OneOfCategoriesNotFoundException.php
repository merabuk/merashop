<?php

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class OneOfCategoriesNotFoundException extends CatalogDomainException implements ClientFacingExceptionInterface, NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::OneOfCategoriesNotFound->value;
    }
}
