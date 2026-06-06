<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\ValueObject\Category\Translation;
use JsonSerializable;

final readonly class GetCategoryListResource implements JsonSerializable
{
    /**
     * @param CategoryTranslationItemResource[] $translations
     */
    public function __construct(
        public string $ulid,
        public string $slug,
        public array $translations,
        public int $version,
    ) {
    }

    public static function fromCategory(Category $category): self
    {
        return new self(
            ulid: $category->getUlid()->value(),
            slug: $category->getSlug()->value(),
            translations: array_map(
                fn (Translation $translation) => CategoryTranslationItemResource::fromTranslation($translation),
                $category->getTranslations()->all()
            ),
            version: $category->getVersion()->value(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'ulid' => $this->ulid,
            'slug' => $this->slug,
            'translations' => array_map(
                fn (CategoryTranslationItemResource $translation) => $translation->jsonSerialize(),
                $this->translations
            ),
            'version' => $this->version,
        ];
    }
}
