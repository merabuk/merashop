<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\ValueObject;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Stringable;
use Traversable;

final readonly class ScopeCollection implements Stringable, Countable, IteratorAggregate
{
    use ValueObjectEqualityTrait;

    /**
     * @var Scope[]
     */
    private array $scopes;

    /**
     * @param Scope[] $scopes
     */
    public function __construct(array $scopes)
    {
        $this->scopes = array_values(array_unique($scopes, SORT_REGULAR));
    }

    /**
     * @param string[] $scopes
     */
    public static function fromStrings(array $scopes): self
    {
        return new self(array_map(fn (string $scope) => new Scope($scope), $scopes));
    }

    /**
     * @return string[]
     */
    public function toStrings(): array
    {
        return array_map(fn (Scope $scope) => $scope->value(), $this->scopes);
    }

    /**
     * @return Scope[]
     */
    public function all(): array
    {
        return $this->scopes;
    }

    public function count(): int
    {
        return count($this->scopes);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->scopes);
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
