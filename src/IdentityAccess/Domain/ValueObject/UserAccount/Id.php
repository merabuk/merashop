<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;

final readonly class Id extends UnsignedIntegerId
{
    /**
     * @throws InvalidUserAccountIdException
     */
    public function __construct(int $id)
    {
        try {
            parent::__construct(id: $id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidUserAccountIdException::becauseItIsNotAValidId();
        }
    }

    /**
     * @throws InvalidUserAccountIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
