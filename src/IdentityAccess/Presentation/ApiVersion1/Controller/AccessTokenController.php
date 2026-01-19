<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\ApiVersion1\Controller;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\Exceptions\GrantHandlerException;
use App\IdentityAccess\Application\Exceptions\UnsupportedGrantTypeException;
use App\IdentityAccess\Application\Service\OAuth2TokenService;
use App\IdentityAccess\Presentation\ApiVersion1\Request\AccessTokenRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AccessTokenController extends AbstractController
{
    public function __construct(
        private readonly OAuth2TokenService $tokenService,
    ) {
    }

    /**
     * @throws GrantHandlerException
     * @throws UnsupportedGrantTypeException
     */
    #[Route('/auth/token', name: 'identity_access_api_v1_auth_token', methods: [Request::METHOD_POST], format: 'json')]
    public function __invoke(#[MapRequestPayload] AccessTokenRequest $request): JsonResponse
    {
        $authData = new OAuth2Data(
            grantType: $request->grant_type,
            username: $request->username,
            password: $request->password,
            clientId: $request->client_id,
            clientSecret: $request->client_secret,
            refreshToken: $request->refresh_token,
        );

        $response = $this->tokenService->handle($authData);

        return $this->json($response);
    }
}
