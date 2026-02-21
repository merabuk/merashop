<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
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
final class Translations implements Stringable, Countable, IteratorAggregate
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
     * @param array<string, array{name?: string}> $data
     *
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    public static function fromArray(array $data): self
    {
        $translations = [];
        foreach ($data as $locale => $item) {
            $translations[$locale] = new Translation(
                locale: $locale,
                name: $item['name'] ?? throw new InvalidAttributeNameException(sprintf('Attribute name is required for locale: %s', $locale)),
            );
        }

        return new self($translations);
    }

    /**
     * @return array<string, array{name: string}>
     */
    public function toArray(): array
    {
        return array_map(fn (Translation $translation) => ['name' => $translation->name], $this->data);
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
