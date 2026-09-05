<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Helpers\ArrayAccess;
use App\Shared\Domain\Helpers\TypeCaster;

final readonly class AttributeOptionMetadataNormalizer
{
    /**
     * @param ?array<string, mixed> $data
     *
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public function denormalize(Type $type, ?array $data): ?AttributeOptionMetadataInterface
    {
        if (!$type->hasOptionMetadata()) {
            return null;
        }

        // TODO[logging]: think about to add logging on data is null

        $safeData = $data ?? [];

        return match ($type->value()) {
            TypeEnum::Dimension => DimensionMetadata::fromFloat(TypeCaster::castToFloat(
                value: ArrayAccess::getByKey(data: $safeData, key: 'base_ratio'),
                default: DimensionMetadata::BASE_RATIO
            )),
            default => throw new InvalidArgumentException(sprintf('Denormalization logic for type "%s" is missing in normalizer', $type)),
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
            default => throw new InvalidArgumentException(sprintf('Normalization logic for class "%s" is missing in normalizer', get_debug_type($vo))),
        };
    }
}
