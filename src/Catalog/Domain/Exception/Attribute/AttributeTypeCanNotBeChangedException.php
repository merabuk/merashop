<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class AttributeTypeCanNotBeChangedException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeTypeCanNotBeChanged->value;
    }
}
