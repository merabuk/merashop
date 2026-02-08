<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final class Ulid extends BaseUlid
{
    /**
     * @throws InvalidProductUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidProductUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidProductUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
