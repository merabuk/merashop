<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Application\Query\GetCategoryList\GetCategoryListQuery;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\SortFieldEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category\GetCategoryListResource;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Attribute\MapPagination;
use App\Shared\Presentation\Http\Request\PaginationRequest;
use App\Shared\Presentation\Http\Response\PaginatedResponseTrait;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class GetCategoryListController extends AbstractController
{
    use AuthIdentityAccessTrait;
    use PaginatedResponseTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.category.list';

    /**
     * @throws HandlerFailedException
     */
    #[Route(
        path: '/categories',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_GET],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapPagination(allowedSortFields: [
            SortFieldEnum::Name->value,
            SortFieldEnum::Slug->value,
        ])] PaginationRequest $pagination,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        QueryBusInterface $queryBus,
    ): JsonResponse {
        $query = new GetCategoryListQuery($pagination->toCriteria());

        /** @var PaginatedResult<Category> $result */
        $result = $queryBus->execute($query);

        return $this->createPaginatedResponse(
            result: $result,
            resourceMapper: fn (Category $category) => GetCategoryListResource::fromCategory($category),
            unit: 'categories'
        );
    }
}
