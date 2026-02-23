<?php

declare(strict_types=1);

namespace App\Shared\Domain\Criteria\Filtering;

use Countable;

final readonly class Filters implements Countable
{
    public function __construct(
        /** @var array<string, mixed> $items */
        private array $items = [],
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
