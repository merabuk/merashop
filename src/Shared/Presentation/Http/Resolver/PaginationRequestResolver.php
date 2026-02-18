<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Resolver;

use App\Shared\Domain\Criteria\Filtering\Filters;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class PaginationRequestResolver implements ValueResolverInterface
{
    /**
     * @return iterable<PaginationRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (PaginationRequest::class !== $argument->getType()) {
            return [];
        }

        $cursor = new Cursor(
            lastSeenIdentifier: $request->query->get('lastSeenId'),
            perPage: $request->query->getInt('perPage', Cursor::DEFAULT_PER_PAGE)
        );

        $filters = new Filters($request->query->all('filter'));

        $sort = null;
        if ($field = $request->query->get('sortField')) {
            $sort = new Sort(
                field: $field,
                direction: strtoupper($request->query->get('sortDir', Sort::ASC))
            );
        }

        yield new PaginationRequest(cursor: $cursor, filters: $filters, sort: $sort);
    }
}
