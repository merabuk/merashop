<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
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
     * @var ?array<string, array{name: string}> $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'shared.common.translations_empty')]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'name' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 1, max: Translation::NAME_MAX_LENGTH),
                ],
            ],
            allowExtraFields: false
        ),
    ])]
    public ?array $translations;

    /**
     * @return string[]
     */
    public static function getAttributeTypes(): array
    {
        return [
            TypeEnum::String->value,
            TypeEnum::Int->value,
            TypeEnum::Boolean->value,
            TypeEnum::Select->value,
        ];
    }

    /**
     * @return array<string, array{name: string}>
     */
    protected function getTranslations(): array
    {
        return $this->translations ?? [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }
}
