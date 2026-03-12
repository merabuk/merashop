<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Product;

use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\CreateProductRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Product\CreateProductResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateProductController extends AbstractController
{
    use AuthIdentityAccessTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.products.create';

    #[Route(
        path: '/products',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] CreateProductRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = $request->toCommand(adminUlid: $identity->id);

        $commandBus->execute($command);

        return new JsonResponse(
            data: new CreateProductResponse(message: $translator->trans(
                id: 'admin.api.v1.product.create.success',
                domain: $translationDomainResolver->resolveIcuDomain('messages')
            )),
            status: Response::HTTP_CREATED
        );
    }
}
