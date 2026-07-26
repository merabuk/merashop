<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidProductPriceIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductPriceIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidProductPriceIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
