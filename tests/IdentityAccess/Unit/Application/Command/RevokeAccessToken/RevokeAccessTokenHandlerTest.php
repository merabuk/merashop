<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Command\RevokeAccessToken;

use App\IdentityAccess\Application\Command\RevokeAccessToken\RevokeAccessTokenCommand;
use App\IdentityAccess\Application\Command\RevokeAccessToken\RevokeAccessTokenHandler;
use App\IdentityAccess\Domain\Security\AccessTokenBlacklistInterface;
use PHPUnit\Framework\TestCase;

class RevokeAccessTokenHandlerTest extends TestCase
{
    public function testItDelegatesWorkToAccessTokenBlackListService(): void
    {
        $tokenBlackListService = $this->createMock(AccessTokenBlacklistInterface::class);
        $command = new RevokeAccessTokenCommand(jti: 'token_jti', expiresAt: 1234);

        $tokenBlackListService->expects(self::once())
            ->method('revoke')
            ->with($command->jti, $command->expiresAt);

        $handler = new RevokeAccessTokenHandler($tokenBlackListService);
        $handler($command);
    }
}
