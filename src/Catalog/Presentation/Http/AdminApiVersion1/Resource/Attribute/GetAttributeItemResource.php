<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use JsonSerializable;

final readonly class GetAttributeItemResource implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $code,
        public string $type,
        /**
         * @var AttributeTranslationItemResource[] $translations
         */
        public array $translations,
        public int $version,
        /**
         * @var AttributeOptionItemResource[] $options
         */
        public array $options,
    ) {
    }

    public static function fromAttribute(Attribute $attribute): self
    {
        return new self(
            id: $attribute->getId()->value(),
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: array_map(
                fn (Translation $translation) => AttributeTranslationItemResource::fromTranslation($translation),
                $attribute->getTranslations()->all()
            ),
            version: $attribute->getVersion()->value(),
            options: array_map(
                fn (AttributeOption $option) => AttributeOptionItemResource::fromOption($option),
                $attribute->getOptions()->all()
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'translations' => array_map(
                fn (AttributeTranslationItemResource $translation) => $translation->jsonSerialize(),
                $this->translations
            ),
            'version' => $this->version,
            'options' => array_map(
                fn (AttributeOptionItemResource $option) => $option->jsonSerialize(),
                $this->options
            ),
        ];
    }
}
