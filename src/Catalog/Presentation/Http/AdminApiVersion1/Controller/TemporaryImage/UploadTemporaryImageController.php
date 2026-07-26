<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\TemporaryImage;

use App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage\UploadTemporaryImageRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\TemporaryImage\UploadedTemporaryImageResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Helpers\TypeCastingTrait;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Helper\Traits\ResponseMessageTrait;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadTemporaryImageController extends AbstractController
{
    use AuthIdentityAccessTrait;
    use TypeCastingTrait;
    use ResponseMessageTrait;

    public const string ROUTE_NAME = 'catalog.admin.api.v1.temporary-images.upload';

    #[Route(
        path: '/temporary-images/upload',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: 'multipart/form-data',
    )]
    public function __invoke(
        UploadTemporaryImageRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = $request->toCommand();

        $imageId = $commandBus->execute($command);

        return new JsonResponse(
            data: new UploadedTemporaryImageResponse(
                message: $this->makeSuccessMessageForEntity(
                    translator: $translator,
                    translationDomainResolver: $translationDomainResolver,
                    messageKey: 'messages.temporary_image.upload_success',
                    entityTranslationKey: 'common.temporary_image.entityName',
                    moduleTranslationDomain: 'catalog',
                ),
                imageId: self::castToString(value: $imageId),
            ),
            status: Response::HTTP_CREATED
        );
    }
}
