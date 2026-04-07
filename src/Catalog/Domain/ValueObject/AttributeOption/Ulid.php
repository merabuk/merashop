<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Identity\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidAttributeOptionUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidAttributeOptionUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidAttributeOptionUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
