<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;

final class AttributeOptionRequest
{
    use ValidateLocalesTrait;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Code::MAX_LENGTH)]
    public ?string $code;

    /**
     * @var ?AttributeOptionTranslationRequest[] $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'shared.common.translations_empty')]
    #[Assert\Valid]
    public ?array $translations;

    #[Assert\NotNull]
    #[Assert\Type(type: 'bool')]
    public ?bool $isActive;

    #[Assert\NotNull(groups: [TypeEnum::Dimension->value])]
    #[Assert\Type(type: 'float', groups: [TypeEnum::Dimension->value])]
    #[Assert\Positive(groups: [TypeEnum::Dimension->value])]
    public ?float $baseRatio = null;

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
