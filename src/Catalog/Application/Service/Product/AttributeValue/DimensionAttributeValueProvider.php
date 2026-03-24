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
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
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
     * @throws InvalidProductAttributeMagnitudeDimensionValueException
     * @throws ProductAttributeValueStateException
     * @throws AttributeOptionNotFoundException
     */
    public function handle(Attribute $attribute, AttributeValueDataInterface $data): array
    {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof DimensionAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: DimensionAttributeValueData::class);
        }

        $unitOption = $attribute->getOptions()->getById($data->unitOptionId)
            ?? throw new AttributeOptionNotFoundException();

        return [
            ProductAttributeValue::create(
                attributeId: $attribute->getId(),
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
