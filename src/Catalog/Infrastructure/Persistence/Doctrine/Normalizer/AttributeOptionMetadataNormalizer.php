<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Shared\Domain\Exception\InvalidArgumentException;

final readonly class AttributeOptionMetadataNormalizer
{
    /**
     * @param ?array<string, mixed> $data
     *
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public function denormalize(Type $type, ?array $data): ?AttributeOptionMetadataInterface
    {
        if (null === $data || !$type->hasOptionMetadata()) {
            return null;
        }

        return match ($type->value()) {
            TypeEnum::Dimension => DimensionMetadata::fromFloat((float) ($data['base_ratio'] ?? DimensionMetadata::BASE_RATIO)),
            default => throw new InvalidArgumentException(sprintf('Normalization for type %s not implemented', $type->value()->value)),
        };
    }

    /**
     * @return ?array<string, mixed>
     */
    public function normalize(?AttributeOptionMetadataInterface $vo): ?array
    {
        if (null === $vo) {
            return null;
        }

        return match (true) {
            $vo instanceof DimensionMetadata => ['base_ratio' => $vo->getBaseRatio()],
            default => throw new InvalidArgumentException(sprintf('Denormalization for %s not implemented', get_debug_type($vo))),
        };
    }
}
