<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final class Ulid extends BaseUlid
{
    /**
     * @throws InvalidCategoryUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidCategoryUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidCategoryUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
