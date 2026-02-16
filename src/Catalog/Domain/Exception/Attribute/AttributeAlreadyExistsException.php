<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;


use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;

class AttributeAlreadyExistsException extends CatalogConflictException
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeAlreadyExists->value;
    }
}
