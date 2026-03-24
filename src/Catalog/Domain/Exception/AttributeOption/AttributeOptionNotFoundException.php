<?php

namespace App\Catalog\Domain\Exception\AttributeOption;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class AttributeOptionNotFoundException extends CatalogDomainException implements ClientFacingExceptionInterface, NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeOptionNotFound->value;
    }
}
