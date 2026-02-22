<?php

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class AttributeNotFoundException extends CatalogDomainException implements NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeNotFound->value;
    }
}
