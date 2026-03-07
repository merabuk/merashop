<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category\UpdateCategoryRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category\UpdateCategoryResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Shared\Presentation\Http\ApiRouteParams;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateCategoryController extends AbstractController
{
    use AuthIdentityAccessTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.categories.update';

    #[Route(
        path: '/categories/{id}',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::POSITIVE_INT],
        defaults: [
            ApiRouteParams::ENTITY_LABEL => 'common.category.entityName',
            ApiRouteParams::ENTITY_DOMAIN => 'catalog',
        ],
        methods: [Request::METHOD_PUT],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateCategoryRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = $request->toCommand(id: $id, adminUlid: $identity->id);

        $commandBus->execute($command);

        return new JsonResponse(
            data: new UpdateCategoryResponse(message: $translator->trans(
                id: 'admin.api.v1.category.update.success',
                domain: $translationDomainResolver->resolveIcuDomain('messages')
            ))
        );
    }
}
