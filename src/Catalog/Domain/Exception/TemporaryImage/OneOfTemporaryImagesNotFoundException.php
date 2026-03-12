<?php

namespace App\Catalog\Domain\Exception\TemporaryImage;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class OneOfTemporaryImagesNotFoundException extends CatalogDomainException implements NotFoundExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::OneOfTemporaryImagesNotFoundException->value;
    }
}
