<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;

class MultiSelectAttributeValueProvider implements ProductAttributeValueProviderInterface
{
    use ProductAttributeValueProviderTrait;

    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @throws InvalidAttributeIdException
     * @throws InvalidAttributeOptionIdException
     * @throws ProductAttributeValueStateException
     */
    public function handle(Attribute $attribute, ProductAttributeValueData $data): array
    {
        $this->checkAttributeType($attribute);

        $valueData = $data->value;
        if (!$valueData instanceof MultiSelectAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $valueData::class, expectedClass: MultiSelectAttributeValueData::class);
        }

        // TODO[attribute value]: add check if options exists for attribute

        return array_map(
            fn (int $id) => ProductAttributeValue::createWithOption(
                attributeId: AttributeId::fromInt($data->attributeId),
                attributeOptionId: AttributeOptionId::fromInt($id)
            ),
            $valueData->optionIds
        );
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::MultiSelect;
    }
}
