<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Exception\ProductAttributeValue\UnsupportedAttributeTypeException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\Id;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;

class ProductAttributeValue
{
    /**
     * @throws ProductAttributeValueStateException
     */
    public function __construct(
        private readonly AttributeId $attributeId,
        private ?AttributeOptionId $attributeOptionId = null,
        private ?AttributeValueInterface $value = null,
        private readonly ?Id $id = null,
    ) {
        $this->ensureIsValidState();
    }

    /**
     * @throws ProductAttributeValueStateException
     */
    public static function createWithOption(AttributeId $attributeId, AttributeOptionId $attributeOptionId): self
    {
        return new self(attributeId: $attributeId, attributeOptionId: $attributeOptionId);
    }

    /**
     * @throws ProductAttributeValueStateException
     */
    public static function createWithValue(AttributeId $attributeId, AttributeValueInterface $value): self
    {
        return new self(attributeId: $attributeId, value: $value);
    }

    /**
     * @throws ProductAttributeValueStateException
     */
    public static function create(
        AttributeId $attributeId,
        ?AttributeOptionId $attributeOptionId = null,
        ?AttributeValueInterface $value = null,
    ): self {
        return new self(
            attributeId: $attributeId,
            attributeOptionId: $attributeOptionId,
            value: $value
        );
    }

    public function updateValue(AttributeValueInterface $value): void
    {
        $this->value = $value;
    }

    /**
     * @deprecated
     * @throws UnsupportedAttributeTypeException
     */
    public static function resolveValue(mixed $value): AttributeValueInterface
    {
        return match (true) {
            is_string($value) => StringValue::fromString($value),
            is_int($value) => IntegerValue::fromInt($value),
            is_bool($value) => BooleanValue::fromBool($value),
            is_array($value) => ArrayValue::fromArray($value),
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

    public function getAttributeOptionId(): ?AttributeOptionId
    {
        return $this->attributeOptionId;
    }

    public function getValue(): ?AttributeValueInterface
    {
        return $this->value;
    }

    /**
     * @throws ProductAttributeValueStateException
     */
    private function ensureIsValidState(): void
    {
        if (null === $this->attributeOptionId && null === $this->value) {
            throw ProductAttributeValueStateException::becauseAllFieldsAreNull(['attributeOptionId', 'value']);
        }

        if ($this->attributeOptionId && $this->value) {
            throw ProductAttributeValueStateException::becauseAllFieldsAreNotNull(['attributeOptionId', 'value']);
        }
    }
}
