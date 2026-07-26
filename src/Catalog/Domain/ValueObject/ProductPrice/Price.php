<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceAmountException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceCurrencyException;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Service\Utility\CurrencyHelper;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Price implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MIN_AMOUNT = 0;

    /**
     * @throws InvalidProductPriceAmountException
     */
    public function __construct(
        private int $amount,
        private CurrencyEnum $currency,
    ) {
        if ($this->amount < self::MIN_AMOUNT) {
            throw InvalidProductPriceAmountException::becauseItMustBePositive();
        }
    }

    /**
     * @throws InvalidProductPriceCurrencyException
     * @throws InvalidProductPriceAmountException
     */
    public static function fromPrimitives(int $amount, string $currency): self
    {
        $currencyEnum = CurrencyEnum::tryFrom(mb_strtoupper(mb_trim($currency)));
        if (null === $currencyEnum) {
            throw InvalidProductPriceCurrencyException::becauseItIsNotAValidCurrencyCode();
        }

        return new self($amount, $currencyEnum);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency->value;
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%d_%s', $this->amount, $this->currency->value);
    }

    public function __toString(): string
    {
        return CurrencyHelper::formatPrice($this->amount, $this->currency);
    }
}
