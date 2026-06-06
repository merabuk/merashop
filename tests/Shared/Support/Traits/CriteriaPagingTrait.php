<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Criteria\Sorting\Sort;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin WebTestCase
 */
trait CriteriaPagingTrait
{
    protected const string PER_PAGE_FIELD = 'perPage';
    protected const string FILTERS_FIELD = 'filters';
    protected const string SORT_FIELD = 'sortField';
    protected const string SORT_DIRECTION_FIELD = 'sortDir';

    public static function invalidPerPageProvider(): iterable
    {
        yield 'negative value' => [-1];
        yield 'zero value' => [0];
        yield 'above limit' => [101];
    }

    protected function assertResponseHasContentRange(string $unit, int $perPage, int $total): void
    {
        self::assertResponseHeaderSame('Content-Range', sprintf('%s %d/%d', $unit, $perPage, $total));
    }

    public static function sortDirectionProvider(): iterable
    {
        yield 'invalid direction' => ['invalid_direction', Response::HTTP_UNPROCESSABLE_ENTITY];

        $validCases = ['asc', 'desc', Sort::ASC, Sort::DESC];
        foreach ($validCases as $validCase) {
            yield 'direction '.$validCase => [$validCase, Response::HTTP_OK];
        }
    }
}
