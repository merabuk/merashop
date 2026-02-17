<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Controller;

use App\IdentityAccess\Application\Command\IssueAccessToken\IssueAccessTokenCommand;
use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Request\AccessTokenRequest;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class AccessTokenController extends AbstractController
{
    #[Route(
        path: '/auth/token',
        name: 'identity_access.api.v1.auth.token',
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] AccessTokenRequest $request,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $accountType = match (GrantTypeEnum::tryFrom((string) $request->grant_type)) {
            GrantTypeEnum::Password => IdentityTypeEnum::User,
            GrantTypeEnum::ClientCredentials => IdentityTypeEnum::Module,
            default => null,
        };

        $authData = new OAuth2Data(
            grantType: (string) $request->grant_type,
            username: $request->username,
            password: $request->password,
            accountType: $accountType,
            clientId: $request->client_id,
            clientSecret: $request->client_secret,
            refreshToken: $request->refresh_token,
        );
        $command = new IssueAccessTokenCommand($authData);

        $response = $commandBus->execute($command);

        return $this->json($response);
    }
}
