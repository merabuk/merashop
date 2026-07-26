<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidProductAttributeValueIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductAttributeValueIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidProductAttributeValueIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
