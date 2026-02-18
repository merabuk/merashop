<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Application\Query\GetAttributeList\GetAttributeListQuery;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute\GetAttributeListResponse;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use App\Shared\Presentation\Http\Response\PaginatedResponseTrait;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class GetAttributeListController extends AbstractController
{
    use AuthIdentityAccessTrait;
    use PaginatedResponseTrait;

    /**
     * @throws HandlerFailedException
     */
    #[Route(
        path: '/attributes',
        name: 'catalog.admin.api.v1.attributes.list',
        methods: [Request::METHOD_GET],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        PaginationRequest $pagination,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        QueryBusInterface $queryBus,
    ): JsonResponse {
        $query = new GetAttributeListQuery(
            criteria: new Criteria(
                cursor: $pagination->cursor,
                filters: $pagination->filters,
                sort: $pagination->sort
            )
        );

        /** @var PaginatedResult $result */
        $result = $queryBus->execute($query);

        return $this->createPaginatedResponse(
            result: $result,
            resourceMapper: fn (Attribute $attr) => GetAttributeListResponse::fromAttribute($attr),
            unit: 'attributes'
        );
    }
}
