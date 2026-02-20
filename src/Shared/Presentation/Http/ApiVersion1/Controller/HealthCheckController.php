<?php

namespace App\Shared\Presentation\Http\ApiVersion1\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HealthCheckController
{
    public const string ROUTE_NAME = 'shared.api.v1.health-check';

    #[Route(path: 'health-check', name: self::ROUTE_NAME, methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        return new JsonResponse(['status' => 'OK']);
    }
}
