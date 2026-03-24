<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption\Metadata;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionDimensionMetadataException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class DimensionMetadata implements AttributeOptionMetadataInterface
{
    use ValueObjectEqualityTrait;

    public const float BASE_RATIO = 1.0;

    private float $value;

    /**
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public function __construct(float $value)
    {
        $this->ensureIsValidValue($value);

        $this->value = $value;
    }

    /**
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public static function fromFloat(float $baseRatio): self
    {
        return new self($baseRatio);
    }

    /**
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    public static function fromNullableFloat(?float $baseRatio): self
    {
        return self::fromFloat($baseRatio ?? self::BASE_RATIO);
    }

    public function getBaseRatio(): float
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): float
    {
        return $this->value;
    }

    /**
     * @throws InvalidAttributeOptionDimensionMetadataException
     */
    private function ensureIsValidValue(float $baseRatio): void
    {
        if ($baseRatio < 0) {
            throw InvalidAttributeOptionDimensionMetadataException::becauseItIsNotAValidBaseRatio();
        }
    }
}
