<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Command\RevokeAccessToken;

use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
class RevokeAccessTokenHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly AccessTokenBlacklistInterface $blacklist,
    ) {
    }

    public function __invoke(RevokeAccessTokenCommand $command): void
    {
        $this->blacklist->revoke($command->jti, $command->expiresAt);
    }
}
