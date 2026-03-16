<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use JsonException;

final readonly class ArrayValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private array $values,
    ) {
        // TODO: decide which value type use instead of mixed. Maybe only string?
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    /**
     * @return array<string, mixed>
     */
    public function value(): array
    {
        return $this->values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * @throws JsonException
     */
    protected function getPrimitiveValue(): string
    {
        $values = $this->values;

        sort($values);

        return json_encode($values, JSON_THROW_ON_ERROR);
    }
}
