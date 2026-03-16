<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\ProductPrice\Id;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use DateTimeImmutable;

class ProductPrice
{
    /**
     * @throws ProductPriceStateException
     */
    public function __construct(
        private Price $price,
        private Type $type,
        private Tax $tax,
        private TaxIncludedFlag $taxIncluded,
        private ?ValidityPeriod $validityPeriod = null,
        private readonly ?Id $id = null,
    ) {
        $this->ensureIsValidState();
    }

    public function getAmountWithTax(): int
    {
        $baseAmount = $this->price->getAmount();

        if ($this->taxIncluded->isTrue()) {
            return $baseAmount;
        }

        return $baseAmount + $this->tax->calculateFor($baseAmount);
    }

    public function isActive(DateTimeImmutable $now): bool
    {
        if (false === $this->type->isTimeLimited()) {
            return true;
        }

        return $this->validityPeriod->contains($now);
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getTax(): Tax
    {
        return $this->tax;
    }

    public function getTaxIncluded(): TaxIncludedFlag
    {
        return $this->taxIncluded;
    }

    public function getValidityPeriod(): ?ValidityPeriod
    {
        return $this->validityPeriod;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    /**
     * @throws ProductPriceStateException
     */
    private function ensureIsValidState(): void
    {
        if (false === $this->type->isTimeLimited() && null !== $this->validityPeriod) {
            throw ProductPriceStateException::becauseItIsNotTimeLimitedType(['validityPeriod']);
        }

        if ($this->type->isTimeLimited() && null === $this->validityPeriod) {
            throw ProductPriceStateException::becauseItIsTimeLimitedType(['validityPeriod']);
        }
    }
}
