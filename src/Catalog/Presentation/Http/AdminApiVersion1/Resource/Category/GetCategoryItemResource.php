<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\ValueObject\Category\Translation;
use JsonSerializable;

final readonly class GetCategoryItemResource implements JsonSerializable
{
    /**
     * @param CategoryTranslationItemResource[] $translations
     */
    public function __construct(
        public string $id,
        public ?string $parentId,
        public string $slug,
        public string $status,
        public array $translations,
        public int $version,
        public int $sortOrder,
    ) {
    }

    public static function fromCategory(Category $category): self
    {
        return new self(
            id: $category->getUlid()->value(),
            parentId: null,
            slug: $category->getSlug()->value(),
            status: $category->getStatus()->value()->value,
            translations: array_map(
                fn (Translation $translation) => CategoryTranslationItemResource::fromTranslation($translation),
                $category->getTranslations()->all()
            ),
            version: $category->getVersion()->value(),
            sortOrder: $category->getSortOrder()->value(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'parentId' => $this->parentId,
            'slug' => $this->slug,
            'status' => $this->status,
            'translations' => array_map(
                fn (CategoryTranslationItemResource $translation) => $translation->jsonSerialize(),
                $this->translations
            ),
            'version' => $this->version,
            'sortOrder' => $this->sortOrder,
        ];
    }
}
