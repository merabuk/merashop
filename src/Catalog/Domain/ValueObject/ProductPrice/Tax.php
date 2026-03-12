<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTaxException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTypeException;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Tax implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    /**
     * @throws InvalidProductPriceTaxException
     */
    public function __construct(
        private float $value,
        private TaxTypeEnum $type,
    ) {
        if ($this->value < 0) {
            throw InvalidProductPriceTaxException::becauseItIsNotAValidTax();
        }
    }

    /**
     * @throws InvalidProductPriceTaxException
     * @throws InvalidProductPriceTypeException
     */
    public static function fromPrimitives(float $value, string $type): self
    {
        $type = mb_trim($type);

        return match (TaxTypeEnum::tryFrom($type)) {
            TaxTypeEnum::Percentage => self::percentage($value),
            TaxTypeEnum::Fixed => self::fixed($value),
            default => throw InvalidProductPriceTypeException::becauseItIsNotAValidType($type, TaxTypeEnum::getValues()),
        };
    }

    /**
     * @throws InvalidProductPriceTaxException
     */
    public static function percentage(float $rate): self
    {
        return new self($rate, TaxTypeEnum::Percentage);
    }

    /**
     * @throws InvalidProductPriceTaxException
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

    public function __toString(): string
    {
        return $this->getPrimitiveValue();
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%s_%s', number_format($this->value, 2), $this->type->value);
    }
}
