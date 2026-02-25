<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidRoleException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Stringable;
use Traversable;

/**
 * @implements IteratorAggregate<int, Role>
 */
final readonly class RoleCollection implements Stringable, Countable, IteratorAggregate
{
    use ValueObjectEqualityTrait;

    /**
     * @var Role[]
     */
    private array $roles;

    /**
     * @param Role[] $roles
     */
    public function __construct(array $roles)
    {
        $this->roles = array_values(array_unique($roles, SORT_REGULAR));
    }

    /**
     * @param string[] $roles
     *
     * @throws InvalidRoleException
     */
    public static function fromStrings(array $roles): self
    {
        return new self(array_map(fn (string $role) => new Role($role), $roles));
    }

    /**
     * @return string[]
     */
    public function toStrings(): array
    {
        return array_map(fn (Role $role) => $role->value(), $this->roles);
    }

    /**
     * @return Role[]
     */
    public function all(): array
    {
        return $this->roles;
    }

    public function count(): int
    {
        return count($this->roles);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->roles);
    }

    public function contains(string|Role $role): bool
    {
        $searchValue = $role instanceof Role ? $role->value() : $role;

        foreach ($this->roles as $existingRole) {
            if ($existingRole->value() === $searchValue) {
                return true;
            }
        }

        return false;
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
}
