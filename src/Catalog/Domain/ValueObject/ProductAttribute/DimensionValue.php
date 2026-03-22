<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class DimensionValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private float $magnitude,
        private string $unit,
    ) {
    }

    public function magnitude(): float
    {
        return $this->magnitude;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    // TODO[attribute value]: think about necessary this method
    public function value(): string
    {
        return $this->getPrimitiveValue();
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
