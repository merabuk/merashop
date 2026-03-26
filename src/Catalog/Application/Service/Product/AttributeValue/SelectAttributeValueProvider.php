<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;

class SelectAttributeValueProvider implements ProductAttributeValueProviderInterface
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
     * @throws AttributeOptionNotFoundException
     */
    public function handle(Attribute $attribute, AttributeValueDataInterface $data): array
    {
        $this->checkAttributeType($attribute);

        if (false === $data instanceof SelectAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: SelectAttributeValueData::class);
        }

        $option = $attribute->getOptions()->getById($data->optionId)
            ?? throw AttributeOptionNotFoundException::withId($data->optionId);

        return [
            ProductAttributeValue::createWithOption(
                attributeId: $attribute->getId(),
                attributeOptionId: $option->getId(),
            ),
        ];
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Select;
    }
}
