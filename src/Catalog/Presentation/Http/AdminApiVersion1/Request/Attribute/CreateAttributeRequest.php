<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use Symfony\Component\Validator\Constraints as Assert;

class CreateAttributeRequest extends BaseAttributeRequest
{
    /**
     * @var ?AttributeOptionRequest[] $options
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.attribute.options_empty')]
    #[Assert\Valid]
    public ?array $options;

    public function toCommand(string $adminUlid): CreateAttributeCommand
    {
        return new CreateAttributeCommand(
            code: $this->code,
            type: $this->type,
            translations: array_map(fn (AttributeTranslationRequest $t) => new AttributeTranslationData(
                name: $t->name,
            ), $this->translations),
            options: array_map(fn (AttributeOptionRequest $o) => new AttributeOptionData(
                code: $o->code,
                translations: array_map(fn (AttributeOptionTranslationRequest $t) => new AttributeOptionTranslationData(
                    value: $t->value,
                ), $o->translations),
                isActive: $o->isActive,
                baseRatio: $o->baseRatio,
            ), $this->options),
            adminUlid: $adminUlid,
        );
    }
}
