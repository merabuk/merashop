<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionIdException;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueVersionException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\Factory\Contract\ProductAttributeValueFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\AttributeValueInterface;

final readonly class ProductAttributeValueFactory implements ProductAttributeValueFactoryInterface
{
    /**
     * @throws InvalidAdminUlidException
     * @throws InvalidAttributeIdException
     * @throws InvalidAttributeOptionIdException
     * @throws ProductAttributeValueStateException
     * @throws InvalidProductAttributeValueVersionException
     */
    public function createForTest(
        int $attributeId,
        ?int $attributeOptionId,
        ?AttributeValueInterface $value,
        string $createdByUlid,
    ): ProductAttributeValue {
        return ProductAttributeValue::create(
            attributeId: AttributeId::fromInt($attributeId),
            createdBy: AdminUlid::fromString($createdByUlid),
            attributeOptionId: $attributeOptionId ? AttributeOptionId::fromInt($attributeOptionId) : null,
            value: $value,
        );
    }
}
