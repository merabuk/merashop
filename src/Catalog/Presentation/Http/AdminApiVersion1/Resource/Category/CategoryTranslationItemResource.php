<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category;

use App\Catalog\Domain\ValueObject\Category\Translation;
use JsonSerializable;

final class CategoryTranslationItemResource implements JsonSerializable
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {
    }

    public static function fromTranslation(Translation $translation): self
    {
        return new self(
            name: $translation->name,
            description: $translation->description
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
