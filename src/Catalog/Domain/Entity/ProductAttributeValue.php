<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use RuntimeException;

class ProductAttributeValue
{
    public function __construct(
        private readonly ?Id $id,
        private readonly ?ProductId $productId,
        private readonly AttributeId $attributeId,
        private AttributeValueInterface $value,
    ) {
    }

    public static function createWithRawValue(AttributeId $attributeId, mixed $value): self
    {
        $valueObject = match (gettype($value)) {
            'string'  => new StringValue($value),
            'integer' => new IntegerValue($value),
            'boolean' => new BooleanValue($value),
            'array'   => new ArrayValue($value),
            default   => throw new RuntimeException("Unsupported type")
        };

        return new self(
            id: null,
            productId: null,
            attributeId: $attributeId,
            value: $valueObject
        );
    }

    public static function create(
        AttributeId $attributeId,
        AttributeValueInterface $value,
    ): self {
        return new self(
            id: null,
            productId: null,
            attributeId: $attributeId,
            value: $value
        );
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getAttributeId(): AttributeId
    {
        return $this->attributeId;
    }

    public function getValue(): AttributeValueInterface
    {
        return $this->value;
    }

    public function updateValue(AttributeValueInterface $value): void
    {
        $this->value = $value;
    }
}
