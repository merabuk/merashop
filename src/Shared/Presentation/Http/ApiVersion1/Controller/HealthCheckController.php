<?php

namespace App\Shared\Presentation\Http\ApiVersion1\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'health-check', name: 'shared.api.v1.health-check', methods: [Request::METHOD_GET])]
class HealthCheckController
{
    public function __invoke(): Response
    {
        return new JsonResponse(['status' => 'OK']);
    }
}
