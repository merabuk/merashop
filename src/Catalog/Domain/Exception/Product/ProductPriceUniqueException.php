<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;

class ProductPriceUniqueException extends CatalogConflictException
{
    private string $priceType = 'price_type';
    private string $currency = 'currency';

    public static function duplicatePriceTypeForCurrency(string $priceType, string $currency): self
    {
        return new self()
            ->withPriceType($priceType)
            ->withCurrency($currency);
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductPriceUniqueException->value;
    }

    public function withPriceType(string $priceType): self
    {
        $this->priceType = $priceType;

        return $this;
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getMessageData(): array
    {
        return [
            'priceType' => $this->priceType,
            'currency' => $this->currency,
        ];
    }
}
