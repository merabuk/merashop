<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidTemporaryImageUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidTemporaryImageUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidTemporaryImageUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
