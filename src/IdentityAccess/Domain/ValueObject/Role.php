<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleException;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class Role implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    private const string ROLE_PREFIX = 'ROLE_';

    private string $value;

    /**
     * @throws InvalidRoleException
     */
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        if (empty($value)) {
            throw InvalidRoleException::becauseItIsEmpty();
        }

        if (!str_starts_with($value, self::ROLE_PREFIX)) {
            throw InvalidRoleException::becausePrefixIsMissing(self::ROLE_PREFIX, $value);
        }

        $this->value = $value;
    }

    /**
     * @throws InvalidRoleException
     */
    public static function fromString(string $role): self
    {
        return new self($role);
    }

    public function value(): string
    {
        return $this->value;
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
