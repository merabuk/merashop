<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidProductIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidProductIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
