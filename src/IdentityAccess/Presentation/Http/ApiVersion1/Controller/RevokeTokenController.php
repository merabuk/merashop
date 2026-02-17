<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Controller;

use App\IdentityAccess\Application\Command\RevokeAccessToken\RevokeAccessTokenCommand;
use App\IdentityAccess\Application\Security\CurrentAccessTokenContextInterface;
use App\IdentityAccess\Presentation\Http\ApiVersion1\Resource\RevokeTokenResponse;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Presentation\Http\Attribute\CurrentAuthEntityIdentity;
use App\Shared\Presentation\Http\Security\Controller\AuthIdentityAccessTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class RevokeTokenController extends AbstractController
{
    use AuthIdentityAccessTrait;

    #[Route(
        path: '/auth/logout',
        name: 'identity_access.api.v1.auth.logout',
        methods: [Request::METHOD_POST],
        format: JsonEncoder::FORMAT
    )]
    public function __invoke(
        #[CurrentAuthEntityIdentity] AuthIdentity $identity,
        CurrentAccessTokenContextInterface $accessTokenContext,
        CommandBusInterface $commandBus,
    ): JsonResponse {
        $this->denyAccessUnlessNotUser($identity);

        $command = new RevokeAccessTokenCommand(
            jti: $accessTokenContext->getJti(),
            expiresAt: $accessTokenContext->getExpiresAt()
        );

        $commandBus->execute($command);

        return $this->json(data: new RevokeTokenResponse('Token was successfully revoked'));
    }
}
