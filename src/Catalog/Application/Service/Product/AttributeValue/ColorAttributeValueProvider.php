<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\ColorAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeColorValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;

class ColorAttributeValueProvider implements ProductAttributeValueProviderInterface
{
    use ProductAttributeValueProviderTrait;

    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @return ProductAttributeValue[]
     *
     * @throws InvalidAttributeIdException
     * @throws ProductAttributeValueStateException
     * @throws InvalidProductAttributeColorValueException
     */
    public function handle(Attribute $attribute, ProductAttributeValueData $data): array
    {
        $this->checkAttributeType($attribute);

        $valueData = $data->value;
        if (false === $valueData instanceof ColorAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $valueData::class, expectedClass: ColorAttributeValueData::class);
        }

        return [
            ProductAttributeValue::createWithValue(
                attributeId: AttributeId::fromInt($data->attributeId),
                value: ColorValue::fromString($valueData->value),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Color;
    }
}
