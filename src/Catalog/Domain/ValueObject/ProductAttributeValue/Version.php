<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueVersionException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Temporal\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidProductAttributeValueVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductAttributeValueVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidProductAttributeValueVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidProductAttributeValueVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
