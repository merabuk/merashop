<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Validator\Constraints as Assert;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    TypeEnum::String->value => StringAttributeValueRequest::class,
    TypeEnum::Text->value => TextAttributeValueRequest::class,
    TypeEnum::Integer->value => IntegerAttributeValueRequest::class,
    TypeEnum::Float->value => FloatAttributeValueRequest::class,
    TypeEnum::Boolean->value => BooleanAttributeValueRequest::class,
    TypeEnum::Select->value => SelectAttributeValueRequest::class,
    TypeEnum::MultiSelect->value => MultiSelectAttributeValueRequest::class,
    TypeEnum::Color->value => ColorAttributeValueRequest::class,
    TypeEnum::Date->value => DateAttributeValueRequest::class,
    TypeEnum::Url->value => UrlAttributeValueRequest::class,
    TypeEnum::Dimension->value => DimensionAttributeValueRequest::class,
])]
abstract class BaseAttributeValueRequest
{
    public const string BASE_GROUP = 'BaseAttributeValueRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Positive(groups: [self::BASE_GROUP])]
    public int $attributeId;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Choice(
        callback: 'getAttributeTypes',
        message: 'catalog.product_attribute_value.type_invalid',
        groups: [self::BASE_GROUP],
    )]
    public ?string $type;

    public function toData(): ProductAttributeValueData
    {
        return new ProductAttributeValueData(
            attributeId: $this->attributeId,
            value: $this->toValueData(),
        );
    }

    abstract public function toValueData(): AttributeValueDataInterface;

    /**
     * @return string[]
     */
    public static function getAttributeTypes(): array
    {
        return [
            TypeEnum::String->value,
            TypeEnum::Text->value,
            TypeEnum::Integer->value,
            TypeEnum::Float->value,
            TypeEnum::Boolean->value,
            TypeEnum::Select->value,
            TypeEnum::MultiSelect->value,
            TypeEnum::Color->value,
            TypeEnum::Date->value,
            TypeEnum::Url->value,
            TypeEnum::Dimension->value,
        ];
    }
}
