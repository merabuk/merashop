<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Request;

use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;

final readonly class PaginationRequest
{
    public function __construct(
        public Cursor $cursor,
        public Filters $filters,
        public ?Sort $sort = null,
    ) {
    }
}
