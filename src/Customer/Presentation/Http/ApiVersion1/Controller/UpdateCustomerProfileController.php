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

    public const string ROUTE_NAME = 'customer.api.v1.profile.update';

    #[Route(
        path: '/profile',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_PUT],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] UpdateCustomerProfileRequest $request,
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $this->denyAccessUnlessUser($identity);

        $command = new UpdateCustomerProfileCommand(
            userUlid: $identity->id,
            firstName: $request->firstName,
            lastName: $request->lastName,
            phoneNumber: $request->phoneNumber
        );

        $commandBus->execute($command);

        return new JsonResponse(new UpdateCustomerProfileResponse('Profile was successfully updated'));
    }
}
