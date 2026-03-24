<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute\AttributeOption;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;

final readonly class DimensionMetadataProvider implements AttributeOptionMetadataProviderInterface
{
    public static function getDefaultIndexName(): string
    {
        return self::getAttributeType()->value;
    }

    /**
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public function handle(AttributeOptionData $data): DimensionMetadata
    {
        return DimensionMetadata::fromNullableFloat($data->baseRatio);
    }

    protected static function getAttributeType(): TypeEnum
    {
        return TypeEnum::Dimension;
    }
}
