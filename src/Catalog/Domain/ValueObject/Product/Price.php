<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceCurrencyException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final class Price
{
    use ValueObjectEqualityTrait;

    public const int CURRENCY_LENGTH = 3;

    private int $amount;
    private string $currency;

    /**
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductPriceCurrencyException
     */
    public function __construct(int $amount, string $currency)
    {
        if ($amount < 0) {
            throw InvalidProductPriceAmountException::becauseItMustBePositive();
        }

        if (1 !== preg_match('/^[A-Z]{3}$/', $currency)) {
            throw InvalidProductPriceCurrencyException::becauseItIsNotAValidCurrencyCode();
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%d %s', $this->amount, $this->currency);
    }
}
