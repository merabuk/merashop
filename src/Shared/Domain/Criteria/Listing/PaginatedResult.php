<?php

declare(strict_types=1);

namespace App\Shared\Domain\Criteria\Listing;

/**
 * @template-covariant T
 */
final readonly class PaginatedResult
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        public array $items,
        public int $totalCount,
        public ?string $nextCursor = null,
        public ?string $previousCursor = null,
    ) {
    }
}
