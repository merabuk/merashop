<?php

namespace App\Shared\Infrastructure\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/health-check', name: 'health-check', methods: [ Request::METHOD_GET ])]
class HealthCheckController
{
    public function __invoke(): Response
    {
        return new JsonResponse(['status' => 'OK']);
    }
}
