<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Response;

use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use Symfony\Component\HttpFoundation\JsonResponse;

trait PaginatedResponseTrait
{
    /**
     * @template T
     *
     * @param PaginatedResult<T> $result
     */
    protected function createPaginatedResponse(
        PaginatedResult $result,
        callable $resourceMapper,
        string $unit,
    ): JsonResponse {
        $data = array_map($resourceMapper, $result->items);

        $response = new JsonResponse($data);

        $response->headers->set('Content-Range', sprintf('%s %d/%d', $unit, count($data), $result->totalCount));

        if ($result->nextCursor) {
            $response->headers->set('X-Next-Cursor', $result->nextCursor);
        }
        if ($result->previousCursor) {
            $response->headers->set('X-Previous-Cursor', $result->previousCursor);
        }

        return $response;
    }
}
