<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\IssueAccessToken;

use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;
use App\IdentityAccess\Application\Service\OAuth2TokenServiceInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class IssueAccessTokenHandler implements CommandHandlerInterface
{
    public function __construct(
        private OAuth2TokenServiceInterface $tokenService,
    ) {
    }

    /**
     * @throws UnsupportedGrantTypeException
     */
    public function __invoke(IssueAccessTokenCommand $command): TokenResponseData
    {
        return $this->tokenService->handle($command->data);
    }
}
