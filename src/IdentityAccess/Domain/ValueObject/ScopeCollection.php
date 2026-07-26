<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeException;
use App\IdentityAccess\Domain\Exception\ValueObject\InvalidScopeItemException;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

/**
 * @extends AbstractCollection<Scope>
 */
final readonly class ScopeCollection extends AbstractCollection
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<int, Scope> $scopes
     *
     * @throws InvalidScopeItemException
     */
    public function __construct(array $scopes)
    {
        try {
            $this->ensureDataType(items: $scopes);
            parent::__construct(items: $scopes);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidScopeItemException::fromBase($e);
        }
    }

    /**
     * @param string[] $scopes
     *
     * @throws InvalidScopeException
     * @throws InvalidScopeItemException
     */
    public static function fromStrings(array $scopes): self
    {
        /** @var array<int, Scope> $array */
        $array = array_map(fn (string $scope) => new Scope($scope), $scopes);

        return new self($array);
    }

    /**
     * @return string[]
     */
    public function toStrings(): array
    {
        return array_map(fn (Scope $scope) => $scope->value(), $this->items);
    }

    public function contains(string|Scope $scope): bool
    {
        $searchValue = $scope instanceof Scope ? $scope->value() : $scope;

        return array_any($this->items, fn (Scope $existingScope) => $existingScope->value() === $searchValue);
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
        return Scope::class;
    }
}
