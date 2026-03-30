<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Product;

use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\UpdateProductRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Product\UpdateProductResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Shared\Presentation\Http\ApiRouteParams;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Helper\Traits\ResponseMessageTrait;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateProductController extends AbstractController
{
    use AuthIdentityAccessTrait;
    use ResponseMessageTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.products.update';

    #[Route(
        path: '/products',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::POSITIVE_INT],
        defaults: [
            ApiRouteParams::ENTITY_LABEL => 'common.product.entityName',
            ApiRouteParams::ENTITY_DOMAIN => 'catalog',
        ],
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateProductRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = $request->toCommand(id: $id, adminUlid: $identity->id);

        $commandBus->execute($command);

        return new JsonResponse(
            data: new UpdateProductResponse(message: $this->makeSuccessMessageForEntity(
                translator: $translator,
                translationDomainResolver: $translationDomainResolver,
                messageKey: 'common.messages.update_success',
                entityTranslationKey: 'common.product.entityName',
                moduleTranslationDomain: 'catalog',
            )),
        );
    }
}
