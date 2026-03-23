<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class FloatValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private float $value,
    ) {
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function getPrimitiveValue(): float
    {
        return $this->value;
    }
}
