<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\LogicException;

abstract class CatalogDomainException extends LogicException implements CatalogExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CatalogDomainError->value;
    }
}
