<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductImage;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class SortOrder implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int DEFAULT_VALUE = 0;

    private int $value;

    public function __construct(int $sortOrder)
    {
        $this->value = $sortOrder;
    }

    public function value(): int
    {
        return $this->value;
    }

    public static function fromInt(int $sortOrder): self
    {
        return new self($sortOrder);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_VALUE);
    }

    public function next(): self
    {
        return new self($this->value + 1);
    }

    public function greaterThan(self|int $sortOrder): bool
    {
        $value = $sortOrder instanceof self ? $sortOrder->value() : (int) $sortOrder;

        return $this->value > $value;
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value();
    }
}
