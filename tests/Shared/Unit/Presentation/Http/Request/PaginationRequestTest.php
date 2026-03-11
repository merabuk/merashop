<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\Request;

use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginationRequestTest extends TestCase
{
    public function testToCursor(): void
    {
        $request = $this->makePaginationRequest();

        $cursor = $request->toCursor();

        self::assertEquals($request->lastSeenId, $cursor->lastSeenIdentifier);
        self::assertEquals($request->perPage, $cursor->perPage);
    }

    #[DataProvider('sortProvider')]
    public function testToSort(?string $sortField, bool $assetSortIsNull): void
    {
        $request = $this->makePaginationRequest(
            sortField: $sortField
        );

        $sort = $request->toSort();

        if ($assetSortIsNull) {
            self::assertNull($sort);
        } else {
            self::assertEquals($request->sortField, $sort->field);
            self::assertEquals($request->sortDir, $sort->direction);
        }
    }

    public static function sortProvider(): iterable
    {
        yield 'when passed sort field' => ['name', false];
        yield 'when not passed sort field' => [null, true];
    }

    public function testToFilters(): void
    {
        $request = $this->makePaginationRequest(filters: ['active' => 'true']);

        $filters = $request->toFilters();

        self::assertCount(count($request->filters), $filters);
        self::assertEquals($request->filters['active'], $filters->get('active'));
    }

    public function testToCriteria(): void
    {
        $request = $this->makePaginationRequest();

        $criteria = $request->toCriteria();

        self::assertEquals($request->toCursor(), $criteria->cursor);
        self::assertEquals($request->toSort(), $criteria->sort);
        self::assertEquals($request->toFilters(), $criteria->filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @param string[]             $allowedSortFields
     */
    private function makePaginationRequest(
        ?string $sortField = 'name',
        array $filters = ['active' => '1'],
        array $allowedSortFields = ['name'],
    ): PaginationRequest {
        $request = new PaginationRequest();

        $request->setAllowedSortFields($allowedSortFields);

        $request->lastSeenId = '123';
        $request->perPage = Cursor::DEFAULT_PER_PAGE;
        $request->sortField = $sortField;
        $request->sortDir = Sort::ASC;
        $request->filters = $filters;

        return $request;
    }
}
