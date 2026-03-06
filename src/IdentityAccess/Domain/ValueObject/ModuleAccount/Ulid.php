<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountUlidException;
use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid as BaseUlid;

final readonly class Ulid extends BaseUlid
{
    /**
     * @throws InvalidModuleAccountUlidException
     */
    public function __construct(string $ulid)
    {
        try {
            parent::__construct($ulid);
        } catch (InvalidUlidException $e) {
            throw new InvalidModuleAccountUlidException(message: $e->getMessage(), previous: $e);
        }
    }

    /**
     * @throws InvalidModuleAccountUlidException
     */
    public static function fromString(string $ulid): self
    {
        return new self($ulid);
    }
}
