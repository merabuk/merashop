<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\ValueObject\Attribute\Translation;
use JsonSerializable;

final class AttributeTranslationItemResource implements JsonSerializable
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function fromTranslation(Translation $translation): self
    {
        return new self(
            name: $translation->name,
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
