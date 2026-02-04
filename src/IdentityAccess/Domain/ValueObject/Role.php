<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use InvalidArgumentException;
use Stringable;

final readonly class Role implements Stringable
{
    use ValueObjectEqualityTrait;

    public function __construct(private string $role)
    {
        if (empty($this->role)) {
            throw new InvalidArgumentException('Role cannot be empty');
        }

        if (!str_starts_with($this->role, 'ROLE_')) {
            throw new InvalidArgumentException(sprintf('Role must start with ROLE_, got "%s"', $this->role));
        }
    }

    public static function fromString(string $role): self
    {
        return new self($role);
    }

    public function value(): string
    {
        return $this->role;
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
