<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\TextAttributeValueData;
use App\Catalog\Domain\ValueObject\ProductAttribute\LocalizedTextValue;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;

final class TextAttributeValueRequest extends BaseAttributeValueRequest
{
    use ValidateLocalesTrait;

    /**
     * @var array<string, string>
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Length(max: LocalizedTextValue::MAX_LENGTH),
    ])]
    public ?array $translations;

    public function toData(): TextAttributeValueData
    {
        return new TextAttributeValueData(translations: $this->translations);
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
