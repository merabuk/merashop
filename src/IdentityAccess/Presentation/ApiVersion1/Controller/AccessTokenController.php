<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\ApiVersion1\Controller;

use App\IdentityAccess\Application\Service\OAuth2TokenServiceJunie;
use App\IdentityAccess\Presentation\ApiVersion1\Request\AccessTokenRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class AccessTokenController extends AbstractController
{
    public function __construct(
        private readonly OAuth2TokenServiceJunie $tokenService,
    ) {
    }

    #[Route('/auth/token', name: 'identity_access_api_v1_auth_token', methods: [Request::METHOD_POST])]
    public function __invoke(#[MapRequestPayload] AccessTokenRequest $request): JsonResponse
    {
        try {
            $response = $this->tokenService->handle($request);

            return new JsonResponse($response);
        } catch (\InvalidArgumentException|BadCredentialsException $e) {
            return new JsonResponse([
                'error' => 'invalid_grant',
                'error_description' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'server_error',
                'error_description' => 'An unexpected error occurred.',
            ], 500);
        }
    }
}
