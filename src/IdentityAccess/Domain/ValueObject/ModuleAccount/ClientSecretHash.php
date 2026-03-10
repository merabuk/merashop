<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountPasswordHashException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class ClientSecretHash implements EquatableInterface, Stringable
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
        $hash = mb_trim($hash);

        if (empty($hash)) {
            throw InvalidModuleAccountPasswordHashException::becauseItIsEmpty();
        }

        if (self::MAX_LENGTH < mb_strlen($hash)) {
            throw InvalidModuleAccountPasswordHashException::becauseItIsTooLong(self::MAX_LENGTH);
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
