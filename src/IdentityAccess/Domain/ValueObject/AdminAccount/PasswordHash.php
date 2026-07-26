<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountPasswordHashException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class PasswordHash implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $passwordHash;

    /**
     * @throws InvalidAdminAccountPasswordHashException
     */
    private function __construct(string $hash)
    {
        $hash = mb_trim($hash);

        $this->ensureIsValidHash($hash);

        $this->passwordHash = $hash;
    }

    /**
     * @throws InvalidAdminAccountPasswordHashException
     */
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    /**
     * @throws InvalidAdminAccountPasswordHashException
     */
    private function ensureIsValidHash(string $hash): void
    {
        if (empty($hash)) {
            throw InvalidAdminAccountPasswordHashException::becauseItIsEmpty();
        }

        if (self::MAX_LENGTH < mb_strlen($hash)) {
            throw InvalidAdminAccountPasswordHashException::becauseItIsTooLong(self::MAX_LENGTH);
        }
    }

    public function value(): string
    {
        return $this->passwordHash;
    }

    public function __toString(): string
    {
        return $this->value();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
