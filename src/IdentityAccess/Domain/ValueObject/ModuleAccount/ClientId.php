<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject\ModuleAccount;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountClientIdException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class ClientId implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $clientId;

    /**
     * @throws InvalidModuleAccountClientIdException
     */
    public function __construct(string $clientId)
    {
        $trimmed = mb_trim($clientId);

        if (empty($trimmed)) {
            throw new InvalidModuleAccountClientIdException('Client ID cannot be empty');
        }

        $this->clientId = $trimmed;
    }

    /**
     * @throws InvalidModuleAccountClientIdException
     */
    public static function fromString(string $clientId): self
    {
        return new self($clientId);
    }

    public function value(): string
    {
        return $this->clientId;
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
