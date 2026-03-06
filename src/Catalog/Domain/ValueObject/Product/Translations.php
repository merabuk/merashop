<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonException;
use Stringable;
use Traversable;

/**
 * @implements IteratorAggregate<string, Translation>
 */
final readonly class Translations implements Countable, EquatableInterface, IteratorAggregate, Stringable
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<string, Translation> $data
     */
    public function __construct(
        private readonly array $data = [],
    ) {
    }

    /**
     * @return array<string, Translation>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function get(string $locale): ?Translation
    {
        return $this->data[$locale] ?? null;
    }

    /**
     * @param array<string, array{name: string, description?: string}> $data
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public static function fromArray(array $data): self
    {
        $translations = [];
        foreach ($data as $locale => $item) {
            $translations[$locale] = new Translation(
                locale: $locale,
                name: $item['name'],
                description: $item['description'] ?? null
            );
        }

        return new self($translations);
    }

    /**
     * @throws JsonException
     */
    protected function getPrimitiveValue(): string
    {
        $data = $this->data;
        ksort($data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
