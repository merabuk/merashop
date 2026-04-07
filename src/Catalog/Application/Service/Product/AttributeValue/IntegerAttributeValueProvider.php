<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueVersionException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;

class IntegerAttributeValueProvider implements ProductAttributeValueProviderInterface
{
    use ProductAttributeValueProviderTrait;

    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @return ProductAttributeValue[]
     *
     * @throws InvalidProductAttributeValueVersionException
     * @throws ProductAttributeValueStateException
     */
    public function handle(
        Attribute $attribute,
        AttributeValueDataInterface $data,
        AdminUlid $adminUlid,
    ): array {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof IntegerAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: IntegerAttributeValueData::class);
        }

        return [
            ProductAttributeValue::createWithValue(
                attributeId: $attribute->getId(),
                value: IntegerValue::fromInt($data->value),
                createdBy: $adminUlid,
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Integer;
    }
}
