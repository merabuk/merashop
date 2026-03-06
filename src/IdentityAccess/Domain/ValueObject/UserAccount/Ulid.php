<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidUserAccountUlidException
     */
    protected function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException) {
            throw InvalidUserAccountUlidException::becauseItIsNotAValidUlid();
        }
    }

    /**
     * @throws InvalidUserAccountUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
