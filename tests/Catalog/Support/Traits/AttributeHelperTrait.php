<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support\Traits;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translation as AttributeTranslation;
use App\Catalog\Domain\ValueObject\Attribute\Translations as AttributeTranslations;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation as AttributeOptionTranslation;

trait AttributeHelperTrait
{
    protected function fillAndGetCreateCommand(Attribute $attribute): CreateAttributeCommand
    {
        return new CreateAttributeCommand(
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: self::getValidAttributeTranslations($attribute->getTranslations()),
            options: self::getValidAttributeOptions($attribute->getOptions()),
            adminUlid: $attribute->getCreatedBy()->value(),
        );
    }

    protected function fillAndGetUpdateCommand(Attribute $attribute): UpdateAttributeCommand
    {
        return new UpdateAttributeCommand(
            id: $attribute->getId()->value(),
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: self::getValidAttributeTranslations($attribute->getTranslations()),
            version: $attribute->getVersion()->value(),
            adminUlid: $attribute->getUpdatedBy()->value(),
        );
    }

    /**
     * @return AttributeTranslationData[]
     */
    protected static function getValidAttributeTranslations(AttributeTranslations $translations): array
    {
        return array_map(fn (AttributeTranslation $t) => new AttributeTranslationData(
            name: $t->name,
        ), $translations->all());
    }

    /**
     * @return AttributeOptionData[]
     */
    protected static function getValidAttributeOptions(OptionCollection $options): array
    {
        return array_map(fn (AttributeOption $option) => new AttributeOptionData(
            code: $option->getCode()->value(),
            translations: array_map(fn (AttributeOptionTranslation $t) => new AttributeOptionTranslationData(
                value: $t->value,
            ), $option->getTranslations()->all()),
            isActive: $option->isActive()->value(),
        ), $options->all());
    }
}
