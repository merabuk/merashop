<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class SortOrder implements Stringable
{
    use ValueObjectEqualityTrait;

    private int $sortOrder;

    public function __construct(int $sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function value(): int
    {
        return $this->sortOrder;
    }

    public static function fromInt(int $sortOrder): self
    {
        return new self($sortOrder);
    }

    public function next(): self
    {
        return new self($this->sortOrder + 1);
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value();
    }
}
