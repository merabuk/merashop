<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidAdminAccountUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidAdminAccountUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidAdminAccountUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
