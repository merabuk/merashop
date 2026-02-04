<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountPasswordHashException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final readonly class ClientSecretHash implements Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $passwordHash;

    /**
     * @throws InvalidModuleAccountPasswordHashException
     */
    private function __construct(string $hash)
    {
        $this->ensureIsValidHash($hash);

        $this->passwordHash = $hash;
    }

    /**
     * @throws InvalidModuleAccountPasswordHashException
     */
    public static function fromString(string $hash): self
    {
        return new self($hash);
    }

    /**
     * @throws InvalidModuleAccountPasswordHashException
     */
    private function ensureIsValidHash(string $hash): void
    {
        if (empty($hash)) {
            throw new InvalidModuleAccountPasswordHashException('Password hash cannot be empty');
        }

        if (self::MAX_LENGTH < mb_strlen($hash)) {
            throw new InvalidModuleAccountPasswordHashException('Password hash is too long');
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
