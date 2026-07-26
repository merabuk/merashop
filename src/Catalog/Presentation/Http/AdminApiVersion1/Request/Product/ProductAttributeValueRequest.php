<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
final class ProductAttributeValueRequest implements GroupSequenceProviderInterface
{
    private const string BASE_GROUP = 'ProductAttributeValueRequest';

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $attributeId = null;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getAvailableTypes',
        message: 'catalog.product_attribute_value.type_invalid'
    )]
    public ?string $type = null;

    #[Assert\NotNull(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'int', groups: [TypeEnum::Integer->value])]
    #[Assert\Type(type: 'bool', groups: [TypeEnum::Boolean->value])]
    #[Assert\Type(type: 'string', groups: [
        TypeEnum::String->value,
        TypeEnum::Text->value,
    ])]
    #[Assert\Type(type: 'array', groups: [
        TypeEnum::Select->value,
        TypeEnum::MultiSelect->value,
    ])]
    #[Assert\All(constraints: [
        new Assert\Type('int'),
    ], groups: [
        TypeEnum::Select->value,
        TypeEnum::MultiSelect->value,
    ])]
    public mixed $value = null;

    public function getGroupSequence(): array
    {
        $groups = [self::BASE_GROUP];

        if ($type = TypeEnum::tryFrom((string) $this->type)) {
            $groups[] = $type->value;
        }

        return $groups;
    }

    /**
     * @return string[]
     */
    public static function getAvailableTypes(): array
    {
        return [
            TypeEnum::String->value,
            TypeEnum::Text->value,
            TypeEnum::Integer->value,
            TypeEnum::Float->value,
            TypeEnum::Boolean->value,
            TypeEnum::Select->value,
            TypeEnum::MultiSelect->value,
        ];
    }
}
