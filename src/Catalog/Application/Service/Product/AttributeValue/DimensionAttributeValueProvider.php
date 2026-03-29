<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeMagnitudeDimensionValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueVersionException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;

class DimensionAttributeValueProvider implements ProductAttributeValueProviderInterface
{
    use ProductAttributeValueProviderTrait;

    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @return ProductAttributeValue[]
     *
     * @throws AttributeOptionNotFoundException
     * @throws InvalidProductAttributeMagnitudeDimensionValueException
     * @throws InvalidProductAttributeValueVersionException
     * @throws ProductAttributeValueStateException
     */
    public function handle(
        Attribute $attribute,
        AttributeValueDataInterface $data,
        AdminUlid $adminUlid,
    ): array {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof DimensionAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: DimensionAttributeValueData::class);
        }

        $unitOption = $attribute->getOptions()->getById($data->unitOptionId)
            ?? throw AttributeOptionNotFoundException::withId($data->unitOptionId);

        return [
            ProductAttributeValue::create(
                attributeId: $attribute->getId(),
                createdBy: $adminUlid,
                attributeOptionId: $unitOption->getId(),
                value: new DimensionValue(
                    magnitude: $data->magnitude,
                    unit: $unitOption->getId(),
                ),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Dimension;
    }
}
