<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseAttributeRequest
{
    use ValidateLocalesTrait;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Code::MAX_LENGTH)]
    public ?string $code;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getAttributeTypes',
        message: 'catalog.attribute.type_invalid'
    )]
    public ?string $type;

    /**
     * @var ?AttributeTranslationRequest[] $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'shared.common.translations_empty')]
    #[Assert\Valid]
    public ?array $translations;

    /**
     * @return string[]
     */
    public static function getAttributeTypes(): array
    {
        return [
            TypeEnum::String->value,
            TypeEnum::Integer->value,
            TypeEnum::Float->value,
            TypeEnum::Boolean->value,
            TypeEnum::Select->value,
            TypeEnum::MultiSelect->value,
            TypeEnum::Color->value,
            TypeEnum::Date->value,
            TypeEnum::Text->value,
            TypeEnum::Url->value,
            TypeEnum::Image->value,
        ];
    }

    /**
     * @return array<string, true>
     */
    protected function getTranslations(): array
    {
        return isset($this->translations)
            ? array_map(fn (AttributeTranslationRequest $translation) => true, $this->translations)
            : [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }
}
