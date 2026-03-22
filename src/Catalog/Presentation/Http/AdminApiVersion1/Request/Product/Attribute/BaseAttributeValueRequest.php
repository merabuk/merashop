<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Validator\Constraints as Assert;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    TypeEnum::String->value => StringAttributeValueRequest::class,
    TypeEnum::Text->value => TextAttributeValueRequest::class,
    TypeEnum::Int->value => IntegerAttributeValueRequest::class,
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
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $attributeId;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getAttributeTypes',
        message: 'catalog.product_attribute_value.type_invalid'
    )]
    public ?string $type;

    abstract public function toData(): AttributeValueDataInterface;

    /**
     * @return string[]
     */
    public static function getAttributeTypes(): array
    {
        return [
            TypeEnum::String->value,
            TypeEnum::Text->value,
            TypeEnum::Int->value,
            TypeEnum::Float->value,
            TypeEnum::Boolean->value,
            TypeEnum::Select->value,
            TypeEnum::MultiSelect->value,
            TypeEnum::Color->value,
            TypeEnum::Date->value,
            TypeEnum::Url->value,
        ];
    }
}
