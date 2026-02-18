<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute\UpdateAttributeRequest;
use App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute\UpdateAttributeResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UpdateAttributeController extends AbstractController
{
    use AuthIdentityAccessTrait;

    /**
     * @throws HandlerFailedException
     */
    #[Route(
        path: '/attributes/{id}',
        name: 'catalog.admin.api.v1.attributes.update',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: [Request::METHOD_PUT],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateAttributeRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $this->denyAccessUnlessAdmin($identity);

        $command = new UpdateAttributeCommand(
            id: $id,
            code: $request->code,
            type: $request->type,
            translations: $request->translations,
            version: $request->version,
            adminUlid: $identity->id,
        );

        try {
            $commandBus->execute($command);
        } catch (HandlerFailedException $e) {
            $previous = $e->getPrevious();

            if ($previous instanceof ConcurrencyException) {
                $previous->withEntityName('Attribute');
            }

            throw $e;
        }

        return new JsonResponse(
            data: new UpdateAttributeResponse('Attribute was successfully updated')
        );
    }
}
