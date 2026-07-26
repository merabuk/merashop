<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleException;
use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleItemException;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

/**
 * @extends AbstractCollection<Role>
 */
final readonly class RoleCollection extends AbstractCollection
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<int, Role> $roles
     *
     * @throws InvalidRoleItemException
     */
    public function __construct(array $roles)
    {
        try {
            $this->ensureDataType(items: $roles);
            parent::__construct(items: $roles);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidRoleItemException::fromBase($e);
        }
    }

    /**
     * @param string[] $roles
     *
     * @throws InvalidRoleException
     * @throws InvalidRoleItemException
     */
    public static function fromStrings(array $roles): self
    {
        /** @var array<int, Role> $array */
        $array = array_map(fn (string $role) => new Role($role), $roles);

        return new self($array);
    }

    /**
     * @return string[]
     */
    public function toStrings(): array
    {
        return array_map(fn (Role $role) => $role->value(), $this->items);
    }

    public function contains(string|Role $role): bool
    {
        $searchValue = $role instanceof Role ? $role->value() : $role;

        return array_any($this->items, fn (Role $existingRole) => $existingRole->value() === $searchValue);
    }

    public function __toString(): string
    {
        return implode(' ', $this->getPrimitiveValue());
    }

    /**
     * @return string[]
     */
    protected function getPrimitiveValue(): array
    {
        $values = $this->toStrings();
        sort($values);

        return $values;
    }

    protected function getExpectedClass(): string
    {
        return Role::class;
    }
}
