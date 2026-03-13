<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category\CreateCategoryRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category\CreateCategoryResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Helper\Traits\ResponseMessageTrait;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateCategoryController extends AbstractController
{
    use AuthIdentityAccessTrait;
    use ResponseMessageTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.categories.create';

    #[Route(
        path: '/categories',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] CreateCategoryRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = $request->toCommand(adminUlid: $identity->id);

        $commandBus->execute($command);

        return new JsonResponse(
            data: new CreateCategoryResponse(message: $this->makeSuccessMessageForEntity(
                translator: $translator,
                translationDomainResolver: $translationDomainResolver,
                messageKey: 'common.messages.create_success',
                entityTranslationKey: 'common.category.entityName',
                moduleTranslationDomain: 'catalog',
            )),
            status: Response::HTTP_CREATED
        );
    }
}
