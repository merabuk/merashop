<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Query\GetCategoryList;

use App\Catalog\Application\Query\GetCategoryList\GetCategoryListHandler;
use App\Catalog\Application\Query\GetCategoryList\GetCategoryListQuery;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCategoryListHandlerTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $perPage = 2;
        $total = 10;
        $query = $this->fillAndGetQuery($perPage);
        $paginatedResult = $this->createPaginatedResult(perPage: $perPage, total: $total);

        $this->readRepository->expects(self::once())
            ->method('paginate')
            ->with($query->criteria)
            ->willReturn($paginatedResult);

        $result = $this->createHandler()($query);

        self::assertSame($paginatedResult, $result);
    }

    public function testItReturnsEmptyResultWhenNoItemsFound(): void
    {
        $query = $this->fillAndGetQuery(perPage: 10);
        $emptyResult = new PaginatedResult(items: [], totalCount: 0, nextCursor: null);

        $this->readRepository->method('paginate')->willReturn($emptyResult);

        $result = $this->createHandler()($query);

        self::assertEmpty($result->items);
        self::assertSame(0, $result->totalCount);
        self::assertNull($result->nextCursor);
    }

    private function createPaginatedResult(int $perPage, int $total): PaginatedResult
    {
        $attributes = [];
        $next = null;

        for ($i = 1; $i <= $perPage; ++$i) {
            $attribute = CategoryMother::createWithData(id: $i);
            $attributes[] = $attribute;
            $next = $attribute->getId()->value();
        }

        return new PaginatedResult(
            items: $attributes,
            totalCount: $total,
            nextCursor: $next ? (string) $next : null
        );
    }

    private function fillAndGetQuery(int $perPage): GetCategoryListQuery
    {
        return new GetCategoryListQuery(
            criteria: new Criteria(
                cursor: new Cursor(perPage: $perPage),
                filters: new Filters()
            )
        );
    }

    private function createHandler(): GetCategoryListHandler
    {
        return new GetCategoryListHandler(readRepository: $this->readRepository);
    }
}
