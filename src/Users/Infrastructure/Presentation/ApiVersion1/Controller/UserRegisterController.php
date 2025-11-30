<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Presentation\ApiVersion1\Controller;

use App\Users\Application\Dto\UserRegisterDto;
use App\Users\Application\Service\UserRegisterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/v1/users/register', name: 'users.api.v1.register', methods: [ Request::METHOD_POST ])]
class UserRegisterController
{
    public function __invoke(Request $request, UserRegisterService $service): Response
    {
        $data = new UserRegisterDto(
            firstName: $request->request->get('firstName'),
            lastName: $request->request->get('lastName'),
            email: $request->request->get('email'),
            password: $request->request->get('password'),
            phoneNumber: $request->request->get('phoneNumber'),
        );

        $event = $service->process($data);

        // dispatch event with bus

        return new JsonResponse(['message' => 'User was successfully registered'], Response::HTTP_CREATED);
    }
}
