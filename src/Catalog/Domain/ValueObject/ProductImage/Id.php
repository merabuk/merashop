<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductImage;

use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidProductImageIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductImageIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidProductImageIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
