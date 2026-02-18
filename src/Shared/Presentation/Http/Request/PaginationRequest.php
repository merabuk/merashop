<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Request;

use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use Symfony\Component\Validator\Constraints as Assert;

final class PaginationRequest
{
    #[Assert\Type('string')]
    public ?string $lastSeenId = null;

    #[Assert\Range(min: 1, max: 100)]
    public int $perPage = Cursor::DEFAULT_PER_PAGE;

    #[Assert\Type('string')]
    public ?string $sortField = null;

    #[Assert\Choice([Sort::ASC, Sort::DESC])]
    public string $sortDir = Sort::ASC;

    /** @var array<string, mixed> */
    public array $filters = [];

    public function toCriteria(): Criteria
    {
        return new Criteria(
            cursor: $this->toCursor(),
            filters: $this->toFilters(),
            sort: $this->toSort()
        );
    }

    public function toCursor(): Cursor
    {
        return new Cursor(
            lastSeenIdentifier: $this->lastSeenId ? mb_trim($this->lastSeenId) : null,
            perPage: $this->perPage
        );
    }

    public function toSort(): ?Sort
    {
        return $this->sortField
            ? new Sort(field: mb_trim($this->sortField), direction: $this->sortDir)
            : null;
    }

    public function toFilters(): Filters
    {
        return new Filters($this->filters);
    }
}
