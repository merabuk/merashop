<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceVersionException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Temporal\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidProductPriceVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductPriceVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidProductPriceVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidProductPriceVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
