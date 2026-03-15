<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class CategorySortOrderOutOfRangeException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CategorySortOrderOutOfRange->value;
    }
}
