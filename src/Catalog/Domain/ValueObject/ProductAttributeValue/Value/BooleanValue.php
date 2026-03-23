<?php

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Shared\Domain\ValueObject\BaseFlag;

final readonly class BooleanValue extends BaseFlag implements AttributeValueInterface
{
    public static function fromBool(bool $value): self
    {
        return new self($value);
    }
}
