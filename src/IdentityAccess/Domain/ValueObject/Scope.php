<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class Scope implements \Stringable
{
    use ValueObjectEqualityTrait;

    public function __construct(private string $scope)
    {
        if (empty($this->scope)) {
            throw new \InvalidArgumentException('Scope cannot be empty');
        }

        if (!preg_match('/^[a-z0-9\._:-]+$/i', $this->scope)) {
            throw new \InvalidArgumentException(sprintf('Invalid scope format: "%s"', $this->scope));
        }
    }

    public static function fromString(string $scope): self
    {
        return new self($scope);
    }

    public function value(): string
    {
        return $this->scope;
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
