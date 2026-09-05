<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\Helpers\TypeCaster;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 *
 * @implements IteratorAggregate<int, T>
 */
abstract readonly class AbstractCollection implements Countable, IteratorAggregate, ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    /**
     * @var array<int, T>
     */
    protected array $items;

    /**
     * @param array<int, T> $items
     */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return array<int, T>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function __toString(): string
    {
        return serialize($this->items);
    }

    /**
     * @return string[]
     */
    protected function getPrimitiveValue(): array
    {
        $values = array_map(static function (mixed $item): string {
            return TypeCaster::castToString(value: $item);
        }, $this->items);

        sort($values);

        return $values;
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getExpectedClass(): string;

    /**
     * @param array<int, T> $items
     *
     * @throws InvalidAbstractCollectionItemException
     */
    protected function ensureDataType(array $items): void
    {
        foreach ($items as $index => $item) {
            if (!$item instanceof ($this->getExpectedClass())) {
                throw InvalidAbstractCollectionItemException::becauseItIsNotAValidCollectionItem($this->getExpectedClass(), $index);
            }
        }
    }
}
