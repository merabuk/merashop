<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final class Ulid extends BaseUlid
{
    /**
     * @throws InvalidCustomerProfileUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidCustomerProfileUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidCustomerProfileUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
