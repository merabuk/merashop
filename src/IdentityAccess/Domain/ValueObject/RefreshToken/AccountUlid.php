<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Identity\Ulid as BaseUlid;

final readonly class AccountUlid extends BaseUlid
{
    /**
     * @throws InvalidRefreshTokenAccountUlidException
     */
    public function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException $e) {
            throw new InvalidRefreshTokenAccountUlidException(message: $e->getMessage(), previous: $e);
        }
    }

    /**
     * @throws InvalidRefreshTokenAccountUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
