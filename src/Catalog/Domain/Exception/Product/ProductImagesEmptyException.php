<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\ConflictExceptionInterface;

final class ProductImagesEmptyException extends InvalidCatalogValueObjectException implements ClientFacingExceptionInterface, ConflictExceptionInterface
{
    public static function activeProductMustHaveAtLeastOneImage(): self
    {
        return new self();
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductImagesCanNotBeEmpty->value;
    }
}
