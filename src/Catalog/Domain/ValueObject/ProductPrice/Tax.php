<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxTypeException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Tax implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MIN_FIXED_TAX_VALUE = 0;
    public const int MIN_PERCENTAGE_TAX_VALUE = 0;
    public const int MAX_PERCENTAGE_TAX_VALUE = 100;

    /**
     * @throws InvalidProductPriceTaxValueException
     */
    public function __construct(
        private float $value,
        private TaxTypeEnum $type,
    ) {
        $this->ensureIsValidTaxAmount();
    }

    /**
     * @throws InvalidProductPriceTaxValueException
     * @throws InvalidProductPriceTaxTypeException
     */
    public static function fromPrimitives(float $value, string $type): self
    {
        $type = mb_trim($type);

        return match (TaxTypeEnum::tryFrom($type)) {
            TaxTypeEnum::Percentage => self::percentage($value),
            TaxTypeEnum::Fixed => self::fixed($value),
            default => throw InvalidProductPriceTaxTypeException::becauseItIsNotAValidType($type, TaxTypeEnum::getValues()),
        };
    }

    /**
     * @throws InvalidProductPriceTaxValueException
     */
    public static function percentage(float $rate): self
    {
        return new self($rate, TaxTypeEnum::Percentage);
    }

    /**
     * @throws InvalidProductPriceTaxValueException
     */
    public static function fixed(float $amount): self
    {
        return new self($amount, TaxTypeEnum::Fixed);
    }

    public function calculateFor(int $baseAmount): int
    {
        $value = match ($this->type) {
            TaxTypeEnum::Percentage => $baseAmount * ($this->value / 100),
            TaxTypeEnum::Fixed => $this->value,
        };

        return (int) round($value, 0, PHP_ROUND_HALF_UP);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getType(): TaxTypeEnum
    {
        return $this->type;
    }

    public function getTypeAsString(): string
    {
        return $this->type->value;
    }

    public function isPercentage(): bool
    {
        return TaxTypeEnum::Percentage === $this->type;
    }

    public function isFixed(): bool
    {
        return TaxTypeEnum::Fixed === $this->type;
    }

    public function __toString(): string
    {
        return $this->getPrimitiveValue();
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%s_%s', number_format($this->value, 2), $this->type->value);
    }

    /**
     * @throws InvalidProductPriceTaxValueException
     */
    private function ensureIsValidTaxAmount(): void
    {
        match ($this->type) {
            TaxTypeEnum::Percentage => (
                $this->value < self::MIN_PERCENTAGE_TAX_VALUE
                || $this->value > self::MAX_PERCENTAGE_TAX_VALUE
            )
                ? throw InvalidProductPriceTaxValueException::becauseItIsNotAValidPercentageValue($this->value) : null,
            default => (
                $this->value < self::MIN_FIXED_TAX_VALUE
            )
                ? throw InvalidProductPriceTaxValueException::becauseItIsNotAValidTax() : null,
        };
    }
}
