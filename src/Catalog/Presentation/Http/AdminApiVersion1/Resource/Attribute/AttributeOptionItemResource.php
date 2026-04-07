<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use JsonSerializable;

class AttributeOptionItemResource implements JsonSerializable
{
    public function __construct(
        public string $ulid,
        public string $code,
        /**
         * @var AttributeOptionTranslationItemResource[]
         */
        public array $translations,
        public bool $isActive,
        public AttributeOptionMetadataItemResource $metadata,
    ) {
    }

    public static function fromOption(AttributeOption $attributeOption): self
    {
        return new self(
            ulid: $attributeOption->getUlid()->value(),
            code: $attributeOption->getCode()->value(),
            translations: array_map(
                fn (Translation $translation) => AttributeOptionTranslationItemResource::fromTranslation($translation),
                $attributeOption->getTranslations()->all()
            ),
            isActive: $attributeOption->isActive()->value(),
            metadata: AttributeOptionMetadataItemResource::fromMetadata($attributeOption->getMetadata()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'ulid' => $this->ulid,
            'code' => $this->code,
            'translations' => array_map(
                fn (AttributeOptionTranslationItemResource $translation) => $translation->jsonSerialize(),
                $this->translations
            ),
            'isActive' => $this->isActive,
            'metadata' => $this->metadata->jsonSerialize(),
        ], static fn ($value) => null !== $value);
    }
}
