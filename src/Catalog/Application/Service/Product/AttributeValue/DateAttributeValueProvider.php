<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\DateAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DateValue;

final readonly class DateAttributeValueProvider implements ProductAttributeValueProviderInterface
{
    use ProductAttributeValueProviderTrait;

    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @return ProductAttributeValue[]
     *
     * @throws ProductAttributeValueStateException
     * @throws InvalidProductAttributeDateValueException
     */
    public function handle(Attribute $attribute, AttributeValueDataInterface $data): array
    {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof DateAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: DateAttributeValueData::class);
        }

        return [
            ProductAttributeValue::createWithValue(
                attributeId: $attribute->getId(),
                value: DateValue::fromString($data->value),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Date;
    }
}
