<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\ProductPrice\ProductPriceStateException;
use App\Catalog\Domain\ValueObject\ProductPrice\Id;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
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
        private ?ValidFrom $validFrom = null,
        private ?ValidTo $validTo = null,
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

        if ($this->validFrom && $this->validFrom->isAfter($now)) {
            return false;
        }
        if ($this->validTo && $this->validTo->isBefore($now)) {
            return false;
        }

        return true;
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

    public function getValidFrom(): ?ValidFrom
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?ValidTo
    {
        return $this->validTo;
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
        if (false === $this->type->isTimeLimited() && (null !== $this->validFrom || null !== $this->validTo)) {
            throw ProductPriceStateException::becauseItIsNotTimeLimitedType(['validFrom', 'validTo']);
        }

        if ($this->type->isTimeLimited() && (null === $this->validFrom || null === $this->validTo)) {
            throw ProductPriceStateException::becauseItIsTimeLimitedType(['validFrom', 'validTo']);
        }

        if (
            null !== $this->validFrom
            && null !== $this->validTo
            && $this->validFrom->isAfter($this->validTo)
        ) {
            throw ProductPriceStateException::becauseItIsInvalidTimeLimitValues('validFrom', 'validTo');
        }
    }
}
