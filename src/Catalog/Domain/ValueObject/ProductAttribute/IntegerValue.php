<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class IntegerValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private int $value
    ) {}

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function getPrimitiveValue(): int
    {
        return $this->value;
    }
}
