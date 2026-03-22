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
    TypeEnum::Boolean->value => BooleanAttributeValueRequest::class,
    TypeEnum::Select->value => SelectAttributeValueRequest::class,
    TypeEnum::MultiSelect->value => MultiSelectAttributeValueRequest::class,
    TypeEnum::Dimension->value => DimensionAttributeValueRequest::class,
])]
abstract class BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $attributeId;

    abstract public function toData(): AttributeValueDataInterface;
}
