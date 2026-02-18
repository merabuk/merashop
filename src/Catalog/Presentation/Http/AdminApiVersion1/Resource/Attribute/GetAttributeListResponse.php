<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\ValueObject\Attribute\Translation;

final readonly class GetAttributeListResponse
{
    public function __construct(
        public int $id,
        public string $code,
        public string $type,
        /**
         * @var array<string, array{name: string}> $translations
         */
        public array $translations,
        public int $version,
    ) {
    }

    public static function fromAttribute(Attribute $attribute): self
    {
        return new self(
            id: $attribute->getId()->value(),
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: array_map(function (Translation $translation) {
                return [
                    'name' => $translation->name,
                ];
            }, $attribute->getTranslations()->all()),
            version: $attribute->getVersion()->value(),
        );
    }
}
