<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\AdminApiVersion1\Controller;

use App\IdentityAccess\Application\Command\IssueAccessToken\IssueAccessTokenCommand;
use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Presentation\Http\AdminApiVersion1\Request\AccessTokenRequest;
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
    public const string ROUTE_NAME = 'identity_access.admin.api.v1.auth.token';

    /**
     * @throws UnsupportedGrantTypeException
     */
    #[Route(
        path: '/auth/token',
        name: self::ROUTE_NAME,
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[MapRequestPayload] AccessTokenRequest $request,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $accountType = match (GrantTypeEnum::tryFrom((string) $request->grant_type)) {
            GrantTypeEnum::Password => IdentityTypeEnum::Admin,
            GrantTypeEnum::RefreshToken => null,
            default => throw new UnsupportedGrantTypeException('Admins can only use password or refresh_token'),
        };

        $authData = new OAuth2Data(
            grantType: (string) $request->grant_type,
            username: $request->username,
            password: $request->password,
            accountType: $accountType,
            clientId: null,
            clientSecret: null,
            refreshToken: $request->refresh_token,
        );

        $command = new IssueAccessTokenCommand($authData);

        $response = $commandBus->execute($command);

        return $this->json($response);
    }
}
