<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeMagnitudeDimensionValueException;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class DimensionValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    /**
     * @throws InvalidProductAttributeMagnitudeDimensionValueException
     */
    public function __construct(
        private float $magnitude,
        private ?AttributeOptionId $unit,
    ) {
        if ($this->magnitude < 0) {
            throw InvalidProductAttributeMagnitudeDimensionValueException::becauseItIsNotAValidMagnitude();
        }
        if (null === $this->unit) {
            // TODO: handle this case
            throw InvalidProductAttributeMagnitudeDimensionValueException::becauseItIsNotAValidMagnitude();
        }
    }

    public function magnitude(): float
    {
        return $this->magnitude;
    }

    public function getUnitOptionId(): AttributeOptionId
    {
        return $this->unit;
    }

    /**
     * @return array{magnitude: float}
     */
    public function value(): array
    {
        return [
            'magnitude' => $this->magnitude,
        ];
    }

    public function __toString(): string
    {
        return $this->getPrimitiveValue();
    }

    protected function getPrimitiveValue(): string
    {
        return sprintf('%s_%s', number_format($this->magnitude, 2), $this->unit);
    }
}
