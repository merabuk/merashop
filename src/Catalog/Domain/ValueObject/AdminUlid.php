<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Identity\Ulid as BaseUlid;

final readonly class AdminUlid extends BaseUlid
{
    /**
     * @throws InvalidAdminUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidAdminUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidAdminUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
