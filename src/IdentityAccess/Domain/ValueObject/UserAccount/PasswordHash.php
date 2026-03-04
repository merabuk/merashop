<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountPasswordHashException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class PasswordHash implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $passwordHash;

    /**
     * @throws InvalidUserAccountPasswordHashException
     */
    private function __construct(string $hash)
    {
        $this->ensureIsValidHash($hash);

        $this->passwordHash = $hash;
    }

    /**
     * @throws InvalidUserAccountPasswordHashException
     */
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    /**
     * @throws InvalidUserAccountPasswordHashException
     */
    private function ensureIsValidHash(string $hash): void
    {
        $hash = mb_trim($hash);

        if (empty($hash)) {
            throw InvalidUserAccountPasswordHashException::becauseItEmpty();
        }

        if (self::MAX_LENGTH < mb_strlen($hash)) {
            throw InvalidUserAccountPasswordHashException::becauseItIsTooLong(self::MAX_LENGTH);
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
