<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxTypeException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxValueException;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Tax implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    /**
     * @throws InvalidProductPriceTaxValueException
     */
    public function __construct(
        private float $value,
        private TaxTypeEnum $type,
    ) {
        $this->ensureIsValidTaxAmount();
        if ($this->value < 0) {
            throw InvalidProductPriceTaxValueException::becauseItIsNotAValidTax();
        }
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
        return match ($this->type) {
            TaxTypeEnum::Percentage => (int) round($baseAmount * ($this->value / 100)),
            TaxTypeEnum::Fixed => (int) round($this->value),
        };
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getType(): TaxTypeEnum
    {
        return $this->type;
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
            TaxTypeEnum::Percentage => ($this->value < 0 || $this->value > 100)
                ? throw InvalidProductPriceTaxValueException::becauseItIsNotAValidPercentageValue($this->value) : null,
            default => ($this->value < 0)
                ? throw InvalidProductPriceTaxValueException::becauseItIsNotAValidTax() : null,
        };
    }
}
