<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidTemporaryImageIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidTemporaryImageIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidTemporaryImageIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
