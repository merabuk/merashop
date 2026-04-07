<?php

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeColorValueException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class ColorValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public const string HEX_REGEX = '/^#([A-Fa-f\d]{6}|[A-Fa-f\d]{3})$/';

    private string $value;

    /**
     * @throws InvalidProductAttributeColorValueException
     */
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        if (!preg_match(self::HEX_REGEX, $value)) {
            throw InvalidProductAttributeColorValueException::becauseItIsNotAValidHex($value);
        }

        $this->value = $this->normalize($value);
    }

    /**
     * @throws InvalidProductAttributeColorValueException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value;
    }

    private function normalize(string $hex): string
    {
        $hex = mb_strtoupper($hex);

        if (4 === strlen($hex)) {
            $hex = '#'.$hex[1].$hex[1].$hex[2].$hex[2].$hex[3].$hex[3];
        }

        return $hex;
    }
}
