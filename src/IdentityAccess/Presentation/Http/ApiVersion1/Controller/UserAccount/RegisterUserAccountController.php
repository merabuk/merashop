<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Controller\UserAccount;

use App\IdentityAccess\Application\Command\CreateUserAccount\CreateUserAccountCommand;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Request\UserAccount\RegisterUserAccountRequest;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Resource\UserAccount\UserRegistrationResponse;
use App\Shared\Application\Command\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class RegisterUserAccountController extends AbstractController
{
    public const string ROUTE_NAME = 'identity_access.api.v1.users.register';

    #[Route(
        path: '/users/register',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] RegisterUserAccountRequest $request,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $command = new CreateUserAccountCommand(
            email: $request->getEmail(),
            password: $request->getPassword(),
        );

        $commandBus->execute($command);

        return new JsonResponse(
            data: new UserRegistrationResponse('User was successfully registered'),
            status: Response::HTTP_CREATED
        );
    }
}
