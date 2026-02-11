<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;

class ProductAttributeValue
{
    public function __construct(
        private readonly ?int $id,
        private readonly ProductId $productId,
        private readonly AttributeId $attributeId,
        private mixed $value,
    ) {
    }

    public static function create(
        ProductId $productId,
        AttributeId $attributeId,
        mixed $value,
    ): self {
        return new self(
            id: null,
            productId: $productId,
            attributeId: $attributeId,
            value: $value
        );
    }

    public function getId(): ?int
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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function updateValue(mixed $value): void
    {
        $this->value = $value;
    }
}
