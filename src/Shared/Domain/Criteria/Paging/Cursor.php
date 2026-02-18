<?php

declare(strict_types=1);

namespace App\Shared\Domain\Criteria\Paging;

final readonly class Cursor
{
    public const int DEFAULT_PER_PAGE = 10;

    public function __construct(
        public ?string $lastSeenIdentifier = null,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {
    }
}
