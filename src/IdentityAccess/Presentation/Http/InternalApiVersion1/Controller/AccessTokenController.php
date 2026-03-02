<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\InternalApiVersion1\Controller;

use App\IdentityAccess\Application\Command\IssueAccessToken\IssueAccessTokenCommand;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Presentation\Http\InternalApiVersion1\Request\AccessTokenRequest;
use App\IdentityAccess\Presentation\Http\InternalApiVersion1\Resource\AccessTokenResource;
use App\Shared\Application\Command\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

final class AccessTokenController extends AbstractController
{
    public const string ROUTE_NAME = 'identity_access.internal.api.v1.auth.token';

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
        $command = new IssueAccessTokenCommand($request->getData());

        /** @var TokenResponseData $response */
        $response = $commandBus->execute($command);

        return $this->json(AccessTokenResource::fromDto($response));
    }
}
