<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Application\Query\GetAttributeItem\GetAttributeItemQuery;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute\GetAttributeItemResource;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class GetAttributeItemController extends AbstractController
{
    use AuthIdentityAccessTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.attributes.get';

    /**
     * @throws HandlerFailedException
     */
    #[Route(
        path: '/attributes/{id}',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: [Request::METHOD_GET],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        int $id,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        QueryBusInterface $queryBus,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $query = new GetAttributeItemQuery($id);

        /** @var Attribute $attribute */
        $attribute = $queryBus->execute($query);

        return $this->json(GetAttributeItemResource::fromAttribute($attribute));
    }
}
