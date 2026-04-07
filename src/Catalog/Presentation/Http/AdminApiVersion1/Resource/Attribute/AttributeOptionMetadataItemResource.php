<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use JsonSerializable;

class AttributeOptionMetadataItemResource implements JsonSerializable
{
    public function __construct(
        public string $key,
        public string $value,
    ) {
    }

    public static function fromMetadata(?AttributeOptionMetadataInterface $metadata): self
    {
        $key = match (true) {
            $metadata instanceof DimensionMetadata => 'baseRatio',
            default => 'key',
        };

        return new self(
            key: $key,
            value: (string) $metadata,
        );
    }

    /**
     * @return ?array<string, string>
     */
    public function jsonSerialize(): ?array
    {
        if (!$this->value) {
            return null;
        }

        return [
            $this->key => $this->value,
        ];
    }
}
