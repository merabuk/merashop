<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;

final class InvalidProductPriceAmountException extends InvalidCatalogValueObjectException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Product price amount cannot be empty');
    }

    public static function becauseItMustBePositive(): self
    {
        return new self('Product price amount must be a positive integer');
    }

    public function getErrorCode(): string
    {
        return 'INVALID_PRODUCT_PRICE_AMOUNT';
    }
}
