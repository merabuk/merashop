<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceCurrencyException;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Price implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int CURRENCY_LENGTH = 3;

    private int $amount;
    private CurrencyEnum $currency;

    /**
     * @throws InvalidProductPriceAmountException
     * @throws InvalidProductPriceCurrencyException
     */
    public function __construct(int $amount, string $currency)
    {
        if ($amount < 0) {
            throw InvalidProductPriceAmountException::becauseItMustBePositive();
        }

        $currencyEnum = CurrencyEnum::tryFrom(mb_strtoupper($currency));
        if (null === $currencyEnum) {
            throw InvalidProductPriceCurrencyException::becauseItIsNotAValidCurrencyCode();
        }

        $this->amount = $amount;
        $this->currency = $currencyEnum;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%d %s', $this->amount, $this->currency->value);
    }
}
