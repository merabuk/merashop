<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\TextAttributeValueData;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedTextValue;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class TextAttributeValueRequest extends BaseAttributeValueRequest
{
    use ValidateLocalesTrait;

    /**
     * @var array<string, string>
     */
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Count(min: 1, groups: [self::BASE_GROUP])]
    #[Assert\All(constraints: [
        new Assert\NotBlank(),
        new Assert\Length(max: LocalizedTextValue::MAX_LENGTH),
    ], groups: [self::BASE_GROUP])]
    public ?array $translations;

    public function toValueData(): TextAttributeValueData
    {
        return new TextAttributeValueData(translations: $this->translations);
    }

    #[Assert\Callback(groups: [self::BASE_GROUP])]
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
            ? array_map(fn (string $translation) => true, $this->translations)
            : [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }
}
