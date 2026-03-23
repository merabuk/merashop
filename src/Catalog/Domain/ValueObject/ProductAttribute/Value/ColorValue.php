<?php

namespace App\Catalog\Domain\ValueObject\ProductAttribute\Value;

use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class ColorValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value;
    }
}
