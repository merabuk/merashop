<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Contract\TranslationInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of TranslationInterface
 *
 * @implements IteratorAggregate<string, T>
 */
abstract readonly class AbstractTranslations implements Countable, IteratorAggregate, ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    /**
     * @var array<string, T>
     */
    protected array $items;

    /**
     * @param array<string, T> $items
     */
    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    public function has(string $locale): bool
    {
        return isset($this->items[$locale]);
    }

    /**
     * @return ?T
     */
    public function get(string $locale): mixed
    {
        return $this->items[$locale] ?? null;
    }

    /**
     * @return array<string, T>
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

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
     */
    protected function getPrimitiveValue(): array
    {
        $values = array_map(static fn (mixed $t) => serialize($t), $this->items);
        ksort($values);

        return $values;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function toArray(): array
    {
        return array_map(static fn (TranslationInterface $translation) => $translation->toArray(), $this->items);
    }
}
