<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;

class MultiSelectAttributeValueProvider implements ProductAttributeValueProviderInterface
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

        if (false === $data instanceof MultiSelectAttributeValueData) {
            throw $this->makeInvalidValueDataException(actualClass: $data::class, expectedClass: MultiSelectAttributeValueData::class);
        }

        return array_map(
            fn (int $id) => ProductAttributeValue::createWithOption(
                attributeId: $attribute->getId(),
                attributeOptionId: $this->getOptionId($attribute, $id)
            ),
            $data->optionIds
        );
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::MultiSelect;
    }

    /**
     * @throws AttributeOptionNotFoundException
     */
    private function getOptionId(Attribute $attribute, int $optionId): AttributeOptionId
    {
        $option = $attribute->getOptions()->getById($optionId) ?? throw new AttributeOptionNotFoundException();

        return $option->getId();
    }
}
