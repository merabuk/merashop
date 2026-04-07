<?php

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeUrlValueException;
use App\Shared\Domain\Exception\Services\Validation\InvalidUrlFormatException;
use App\Shared\Domain\Service\Validation\UrlValidator;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class UrlValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    private string $value;

    /**
     * @throws InvalidProductAttributeUrlValueException
     */
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        try {
            $this->value = UrlValidator::normalize(url: $value, allowedSchemes: UrlValidator::ONLY_HTTP);
        } catch (InvalidUrlFormatException $e) {
            throw InvalidProductAttributeUrlValueException::fromBaseException($e);
        }
    }

    /**
     * @throws InvalidProductAttributeUrlValueException
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
}
