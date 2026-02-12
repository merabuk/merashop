<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class StringValue implements AttributeValueInterface
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
