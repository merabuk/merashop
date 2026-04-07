<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductPrice;

use App\Catalog\Domain\Exception\CatalogDomainException;

final class ProductPriceStateException extends CatalogDomainException
{
    /**
     * @param string[] $fields
     */
    public static function becauseItIsNotTimeLimitedType(array $fields): self
    {
        return new self(sprintf(
            'Product price is not time limited type. Fields: %s must be null',
            implode(', ', $fields)
        ));
    }

    /**
     * @param string[] $fields
     */
    public static function becauseItIsTimeLimitedType(array $fields): self
    {
        return new self(sprintf(
            'Product price is time limited type. Fields: %s must be not null',
            implode(', ', $fields)
        ));
    }

    public static function becauseTypeCanNotBeChanged(string $from, string $to): self
    {
        return new self(sprintf('Product price type can not be changed from "%s" to "%s"', $from, $to));
    }

    public static function becauseCurrencyCanNotBeChanged(string $from, string $to): self
    {
        return new self(sprintf('Product price currency can not be changed from "%s" to "%s"', $from, $to));
    }

    public function getErrorCode(): string
    {
        return 'PRODUCT_PRICE_STATE_EXCEPTION';
    }
}
