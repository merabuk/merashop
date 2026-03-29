<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueVersionException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Id;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\AttributeValueInterface;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Version;

class ProductAttributeValue
{
    /**
     * @throws ProductAttributeValueStateException
     */
    public function __construct(
        private readonly AttributeId $attributeId,
        private Version $version,
        private AdminUlid $createdBy,
        private ?AttributeOptionId $attributeOptionId = null,
        private ?AttributeValueInterface $value = null,
        private ?AdminUlid $updatedBy = null,
        private readonly ?Id $id = null,
    ) {
        $this->ensureIsValidState();
    }

    /**
     * @throws InvalidProductAttributeValueVersionException
     * @throws ProductAttributeValueStateException
     */
    public static function createWithOption(
        AttributeId $attributeId,
        AttributeOptionId $attributeOptionId,
        AdminUlid $createdBy,
    ): self {
        return self::create(
            attributeId: $attributeId,
            createdBy: $createdBy,
            attributeOptionId: $attributeOptionId
        );
    }

    /**
     * @throws InvalidProductAttributeValueVersionException
     * @throws ProductAttributeValueStateException
     */
    public static function createWithValue(
        AttributeId $attributeId,
        AttributeValueInterface $value,
        AdminUlid $createdBy,
    ): self {
        return self::create(attributeId: $attributeId, createdBy: $createdBy, value: $value);
    }

    /**
     * @throws InvalidProductAttributeValueVersionException
     * @throws ProductAttributeValueStateException
     */
    public static function create(
        AttributeId $attributeId,
        AdminUlid $createdBy,
        ?AttributeOptionId $attributeOptionId = null,
        ?AttributeValueInterface $value = null,
    ): self {
        return new self(
            attributeId: $attributeId,
            version: Version::initial(),
            createdBy: $createdBy,
            attributeOptionId: $attributeOptionId,
            value: $value
        );
    }

    public function updateValue(AttributeValueInterface $value): void
    {
        $this->value = $value;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getAttributeId(): AttributeId
    {
        return $this->attributeId;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getCreatedBy(): AdminUlid
    {
        return $this->createdBy;
    }

    public function getAttributeOptionId(): ?AttributeOptionId
    {
        return $this->attributeOptionId;
    }

    public function getValue(): ?AttributeValueInterface
    {
        return $this->value;
    }

    public function getUpdatedBy(): ?AdminUlid
    {
        return $this->updatedBy;
    }

    /**
     * @throws ProductAttributeValueStateException
     */
    private function ensureIsValidState(): void
    {
        $allowBoth = $this->value instanceof DimensionValue;

        if ($allowBoth && (null === $this->attributeOptionId || null === $this->value)) {
            throw ProductAttributeValueStateException::becauseOneFieldIsNull(['attributeOptionId', 'value']);
        }

        if (false === $allowBoth && null === $this->attributeOptionId && null === $this->value) {
            throw ProductAttributeValueStateException::becauseAllFieldsAreNull(['attributeOptionId', 'value']);
        }

        if (false === $allowBoth && $this->attributeOptionId && $this->value) {
            throw ProductAttributeValueStateException::becauseAllFieldsAreNotNull(['attributeOptionId', 'value']);
        }
    }
}
