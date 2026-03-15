<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\ProductAttribute\UnsupportedAttributeTypeException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;

class ProductAttributeValue
{
    public function __construct(
        private readonly AttributeId $attributeId,
        private AttributeValueInterface $value,
        private readonly ?Id $id = null,
    ) {
    }

    /**
     * @throws UnsupportedAttributeTypeException
     */
    public static function createWithRawValue(AttributeId $attributeId, mixed $value): self
    {
        return new self(
            attributeId: $attributeId,
            value: self::resolveValue($value),
        );
    }

    public static function create(
        AttributeId $attributeId,
        AttributeValueInterface $value,
    ): self {
        return new self(
            attributeId: $attributeId,
            value: $value
        );
    }

    public function updateValue(AttributeValueInterface $value): void
    {
        $this->value = $value;
    }

    /**
     * @throws UnsupportedAttributeTypeException
     */
    public static function resolveValue(mixed $value): AttributeValueInterface
    {
        return match (true) {
            is_string($value) => new StringValue($value),
            is_int($value) => new IntegerValue($value),
            is_bool($value) => new BooleanValue($value),
            is_array($value) => new ArrayValue($value),
            default => throw UnsupportedAttributeTypeException::becauseIsItNotSupportedType(get_debug_type($value)),
        };
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getAttributeId(): AttributeId
    {
        return $this->attributeId;
    }

    public function getValue(): AttributeValueInterface
    {
        return $this->value;
    }
}
