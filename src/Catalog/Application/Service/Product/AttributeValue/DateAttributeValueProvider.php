<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\DateAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\DateValue;

class DateAttributeValueProvider implements ProductAttributeValueProviderInterface
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
     * @throws InvalidProductAttributeDateValueException
     */
    public function handle(Attribute $attribute, ProductAttributeValueData $data): array
    {
        $this->checkAttributeType($attribute);

        $valueData = $data->value;
        if (false === $valueData instanceof DateAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $valueData::class, expectedClass: DateAttributeValueData::class);
        }

        return [
            ProductAttributeValue::createWithValue(
                attributeId: AttributeId::fromInt($data->attributeId),
                value: DateValue::fromString($valueData->value),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Date;
    }
}
