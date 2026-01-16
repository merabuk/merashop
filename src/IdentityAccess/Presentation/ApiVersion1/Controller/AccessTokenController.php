<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\ApiVersion1\Controller;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\Service\OAuth2TokenService;
use App\IdentityAccess\Presentation\ApiVersion1\Request\AccessTokenRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class AccessTokenController extends AbstractController
{
    public function __construct(
        private readonly OAuth2TokenService $tokenService,
    ) {
    }

    #[Route('/auth/token', name: 'identity_access_api_v1_auth_token', methods: [Request::METHOD_POST])]
    public function __invoke(#[MapRequestPayload] AccessTokenRequest $request): JsonResponse
    {
        try {
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
        } catch (BadCredentialsException $e) {
            // TODO: rework on OAuthServerException error codes and handling through IdentityExceptionListener

            return new JsonResponse([
                'error' => 'invalid_grant',
                'error_description' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            $oauthException = OAuthServerException::serverError($e->getMessage(), previous: $e);

            return $this->createOAuthErrorResponse($oauthException);
        }
    }

    private function createOAuthErrorResponse(OAuthServerException $e): JsonResponse
    {
        $payload = $e->getPayload();

        return new JsonResponse([
            'error' => $payload['error'],
            'error_description' => $payload['message'],
        ], $e->getHttpStatusCode());
    }
}
