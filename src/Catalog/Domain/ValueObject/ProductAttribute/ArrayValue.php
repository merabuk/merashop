<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use JsonException;

final readonly class ArrayValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private array $values
    ) {}

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
        return $this->data[$key] ?? $default;
    }

    /**
     * @throws JsonException
     */
    protected function getPrimitiveValue(): string
    {
        return json_encode($this->values, JSON_THROW_ON_ERROR);
    }
}
