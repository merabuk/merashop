<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use JsonSerializable;

class AttributeOptionTranslationItemResource implements JsonSerializable
{
    public function __construct(
        public string $value,
    ) {
    }

    public static function fromTranslation(Translation $translation): self
    {
        return new self(
            value: $translation->value,
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}
