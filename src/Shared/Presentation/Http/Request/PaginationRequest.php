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
    #[Assert\Choice(
        callback: 'getAllowedSortFields',
        message: 'shared.pagination.sort_field_invalid'
    )]
    public ?string $sortField = null;

    #[Assert\Choice(choices: [Sort::ASC, Sort::DESC])]
    public string $sortDir = Sort::ASC;

    /**
     * @var array<string, mixed>
     */
    public array $filters = [];

    /**
     * @var string[]
     */
    private array $allowedSortFields = [];

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
        return new Filters(items: $this->filters);
    }

    /**
     * @param string[] $fields
     */
    public function setAllowedSortFields(array $fields): void
    {
        $this->allowedSortFields = $fields;
    }

    /**
     * @return string[]
     */
    public function getAllowedSortFields(): array
    {
        return $this->allowedSortFields;
    }
}
