<?php

declare(strict_types=1);

namespace App\Users\Presentation\Http\ApiVersion1\Controller;

use App\Shared\Application\Command\CommandBusInterface;
use App\Users\Application\Command\CreateUser\CreateUserCommand;
use App\Users\Presentation\Http\ApiVersion1\Request\UserRegisterRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

#[Route(path: 'register', name: 'users.api.v1.register', methods: [Request::METHOD_POST], format: JsonEncoder::FORMAT)]
class UserRegisterController
{
    public function __invoke(
        #[MapRequestPayload] UserRegisterRequest $payload,
        CommandBusInterface $commandBus,
    ): Response {
        $command = new CreateUserCommand(
            firstName: $payload->firstName,
            lastName: $payload->lastName,
            email: $payload->email,
            password: $payload->password,
            phoneNumber: $payload->phoneNumber
        );

        $commandBus->execute($command);

        return new JsonResponse(['message' => 'User was successfully registered'], Response::HTTP_CREATED);
    }
}
