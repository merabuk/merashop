<?php

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class BooleanValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private bool $value
    ) {}

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value ? 'Yes' : 'No';
    }

    protected function getPrimitiveValue(): bool
    {
        return $this->value;
    }
}
