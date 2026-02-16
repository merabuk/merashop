<?php

declare(strict_types=1);

namespace App\Customer\Presentation\Http\ApiVersion1\Controller;

use App\Customer\Application\Command\UpdateCustomerProfile\UpdateCustomerProfileCommand;
use App\Customer\Presentation\Http\ApiVersion1\Request\UpdateCustomerProfileRequest;
use App\Customer\Presentation\Http\ApiVersion1\Resource\UpdateCustomerProfileResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UpdateCustomerProfileController extends AbstractController
{
    use AuthIdentityAccessTrait;

    #[Route(
        path: '/profile',
        name: 'customer.api.v1.profile.update',
        methods: [Request::METHOD_PUT],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] UpdateCustomerProfileRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $this->denyAccessUnlessNotUser($identity);

        $command = new UpdateCustomerProfileCommand(
            userUlid: $identity->id,
            firstName: $request->firstName,
            lastName: $request->lastName,
            phoneNumber: $request->phoneNumber
        );

        $commandBus->execute($command);

        // TODO: decide if needed to return updated profile data

        return new JsonResponse(new UpdateCustomerProfileResponse('Profile was successfully updated'));
    }
}
