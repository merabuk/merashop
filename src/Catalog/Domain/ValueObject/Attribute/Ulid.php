<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidAttributeUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidAttributeUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidAttributeUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
