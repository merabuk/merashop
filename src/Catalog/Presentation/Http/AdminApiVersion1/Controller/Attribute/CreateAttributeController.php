<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute\CreateAttributeRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute\CreateAttributeResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class CreateAttributeController extends AbstractController
{
    use AuthIdentityAccessTrait;

    public const ROUTE_NAME = 'catalog.admin.api.v1.attributes.create';

    #[Route(
        path: '/attributes',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] CreateAttributeRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = new CreateAttributeCommand(
            code: $request->code,
            type: $request->type,
            translations: $request->translations,
            adminUlid: $identity->id,
        );

        $commandBus->execute($command);

        return new JsonResponse(
            data: new CreateAttributeResponse('Attribute was successfully created'),
            status: Response::HTTP_CREATED
        );
    }
}
