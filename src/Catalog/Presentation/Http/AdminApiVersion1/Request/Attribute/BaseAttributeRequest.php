<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
abstract class BaseAttributeRequest implements GroupSequenceProviderInterface
{
    use ValidateLocalesTrait;

    protected const string BASE_GROUP = 'BaseAttributeRequest';

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Code::MAX_LENGTH)]
    #[Assert\Regex(pattern: Code::REGEX)]
    public ?string $code = null;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getAttributeTypes',
        message: 'catalog.attribute.type_invalid'
    )]
    public ?string $type = null;

    /**
     * @var ?AttributeTranslationRequest[] $translations
     */
    #[Assert\NotBlank(groups: [AttributeTranslationRequest::BASE_GROUP])]
    #[Assert\Count(
        min: 1,
        minMessage: 'shared.common.translations_empty',
        groups: [AttributeTranslationRequest::BASE_GROUP],
    )]
    #[Assert\Valid(groups: [AttributeTranslationRequest::BASE_GROUP])]
    public ?array $translations = null;

    /**
     * @var ?AttributeOptionRequest[] $options
     */
    #[Assert\NotBlank(groups: [
        TypeEnum::Select->value,
        TypeEnum::MultiSelect->value,
        TypeEnum::Dimension->value,
    ])]
    #[Assert\Count(min: 1, minMessage: 'catalog.attribute.options_empty', groups: [
        TypeEnum::Select->value,
        TypeEnum::MultiSelect->value,
        TypeEnum::Dimension->value,
    ])]
    #[Assert\Valid(groups: [
        AttributeOptionRequest::BASE_GROUP,
        AttributeOptionTranslationRequest::BASE_GROUP,
        TypeEnum::Select->value,
        TypeEnum::MultiSelect->value,
        TypeEnum::Dimension->value,
    ])]
    public ?array $options = null;

    #[Assert\Callback(groups: [AttributeOptionRequest::BASE_GROUP])]
    public function validateUniqueOptions(ExecutionContextInterface $context): void
    {
        if (!isset($this->options)) {
            return;
        }

        $registry = [];
        foreach ($this->options as $index => $option) {
            if (!isset($option->code)) {
                continue;
            }

            if (isset($registry[$option->code])) {
                $context->buildViolation('catalog.attribute.option_code_duplicate')
                    ->atPath("options[{$index}]")
                    ->addViolation();
            }

            $registry[$option->code] = true;
        }
    }

    #[Assert\Callback(groups: [AttributeTranslationRequest::BASE_GROUP])]
    public function validateLocales(ExecutionContextInterface $context): void
    {
        $this->_validateLocales($context);
    }

    public function getGroupSequence(): array
    {
        $groups = [self::BASE_GROUP, AttributeTranslationRequest::BASE_GROUP];

        if ($type = TypeEnum::tryFrom((string) $this->type)) {
            $groups[] = $type->value;

            if ($type->hasOptions()) {
                $groups[] = AttributeOptionRequest::BASE_GROUP;
                $groups[] = AttributeOptionTranslationRequest::BASE_GROUP;
            }
        }

        return $groups;
    }

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
            TypeEnum::Dimension->value,
        ];
    }

    /**
     * @return array<string, true>
     */
    protected function getTranslations(): array
    {
        $translations = [];

        foreach ($this->translations ?? [] as $key => $translation) {
            $translations[(string) $key] = true;
        }

        return $translations;
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }

    /**
     * @return AttributeTranslationData[]
     */
    protected function mapAndGetTranslations(): array
    {
        return array_map(fn (AttributeTranslationRequest $t) => $t->toData(), $this->translations ?? []);
    }

    /**
     * @return AttributeOptionData[]
     */
    protected function mapAndGetOptions(): array
    {
        return array_map(fn (AttributeOptionRequest $o) => $o->toData(), $this->options ?? []);
    }
}
