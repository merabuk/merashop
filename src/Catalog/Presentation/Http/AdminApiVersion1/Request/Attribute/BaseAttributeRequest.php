<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Shared\Domain\Enum\LocaleEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class BaseAttributeRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Code::MAX_LENGTH)]
    public ?string $code;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getAttributeTypes',
        message: 'admin.api.v1.attribute.type.invalid'
    )]
    public ?string $type;

    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'admin.api.v1.attribute.translations.empty')]
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
    /**
     * @var ?array<string, array{name: string}> $translations
     */
    public ?array $translations;

    #[Assert\Callback]
    public function validateLocales(ExecutionContextInterface $context): void
    {
        if (!isset($this->translations) || !is_array($this->translations)) {
            return;
        }

        $validLocales = LocaleEnum::getValues();

        foreach (array_keys($this->translations) as $locale) {
            if (!in_array($locale, $validLocales, true)) {
                $context->buildViolation('admin.api.v1.attribute.locale.invalid')
                    ->setParameter('%locale%', (string) $locale)
                    ->atPath(sprintf('translations[%s]', (string) $locale))
                    ->addViolation();
            }
        }
    }

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
}
