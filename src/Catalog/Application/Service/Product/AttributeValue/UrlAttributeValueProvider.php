<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\UrlAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeUrlValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\UrlValue;

class UrlAttributeValueProvider implements ProductAttributeValueProviderInterface
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
     * @throws InvalidProductAttributeUrlValueException
     */
    public function handle(Attribute $attribute, AttributeValueDataInterface $data): array
    {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof UrlAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: UrlAttributeValueData::class);
        }

        return [
            ProductAttributeValue::createWithValue(
                attributeId: $attribute->getId(),
                value: UrlValue::fromString($data->value),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Url;
    }
}
