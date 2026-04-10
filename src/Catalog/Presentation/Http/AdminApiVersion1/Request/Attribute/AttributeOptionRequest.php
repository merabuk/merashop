<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class AttributeOptionRequest
{
    use ValidateLocalesTrait;

    public const string BASE_GROUP = 'AttributeOptionRequest';

    #[Assert\Optional(groups: [self::BASE_GROUP])]
    #[Assert\Ulid(groups: [self::BASE_GROUP])]
    public ?string $ulid = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Length(min: 1, max: Code::MAX_LENGTH, groups: [self::BASE_GROUP])]
    #[Assert\Regex(pattern: Code::REGEX, groups: [self::BASE_GROUP])]
    public ?string $code;

    /**
     * @var ?AttributeOptionTranslationRequest[] $translations
     */
    #[Assert\NotBlank(groups: [AttributeOptionTranslationRequest::BASE_GROUP])]
    #[Assert\Count(
        min: 1,
        minMessage: 'shared.common.translations_empty',
        groups: [AttributeOptionTranslationRequest::BASE_GROUP],
    )]
    #[Assert\Valid(groups: [AttributeOptionTranslationRequest::BASE_GROUP])]
    public ?array $translations;

    #[Assert\NotNull(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'bool', groups: [self::BASE_GROUP])]
    public ?bool $isActive;

    #[Assert\NotNull(groups: [TypeEnum::Dimension->value])]
    #[Assert\Type(type: 'float', groups: [TypeEnum::Dimension->value])]
    #[Assert\Positive(groups: [TypeEnum::Dimension->value])]
    public ?float $baseRatio = null;

    public function toData(): AttributeOptionData
    {
        return new AttributeOptionData(
            ulid: $this->ulid,
            code: $this->code,
            translations: array_map(fn (AttributeOptionTranslationRequest $t) => $t->toData(), $this->translations),
            isActive: $this->isActive,
            baseRatio: $this->baseRatio,
        );
    }

    #[Assert\Callback(groups: [AttributeOptionTranslationRequest::BASE_GROUP])]
    public function validateLocales(ExecutionContextInterface $context): void
    {
        $this->_validateLocales($context);
    }

    /**
     * @return array<string, true>
     */
    protected function getTranslations(): array
    {
        return isset($this->translations)
            ? array_map(fn (AttributeOptionTranslationRequest $translation) => true, $this->translations)
            : [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }
}
