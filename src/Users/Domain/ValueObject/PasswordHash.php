<?php

declare(strict_types=1);

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserPasswordHashException;

final class PasswordHash implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $passwordHash;

    /**
     * @throws InvalidUserPasswordHashException
     */
    private function __construct(string $hash)
    {
        $this->ensureIsValidHash($hash);

        $this->passwordHash = $hash;
    }

    /**
     * @throws InvalidUserPasswordHashException
     */
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    /**
     * @throws InvalidUserPasswordHashException
     */
    private function ensureIsValidHash(string $hash): void
    {
        if (empty($hash)) {
            throw new InvalidUserPasswordHashException('Password hash cannot be empty');
        }

        if (self::MAX_LENGTH !== mb_strlen($hash)) {
            throw new InvalidUserPasswordHashException('Password hash is too long');
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
