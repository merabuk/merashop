<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductImage;

use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Identity\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidProductImageUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidProductImageUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidProductImageUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
