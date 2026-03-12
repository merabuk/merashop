<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;

class ProductAlreadyExistsException extends CatalogConflictException
{
    public static function becauseSkuAlreadyExists(string $sku): self
    {
        return new self(sprintf('Product with sku "%s" already exists.', $sku));
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductAlreadyExists->value;
    }
}
