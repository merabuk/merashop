<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Command\IssueAccessToken;

use App\IdentityAccess\Application\Command\IssueAccessToken\IssueAccessTokenCommand;
use App\IdentityAccess\Application\Command\IssueAccessToken\IssueAccessTokenHandler;
use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Service\OAuth2TokenServiceInterface;
use App\Tests\Shared\BaseUnitTest;

final class IssueAccessTokenHandlerTest extends BaseUnitTest
{
    public function testItDelegatesWorkToTokenService(): void
    {
        $tokenService = $this->createMock(OAuth2TokenServiceInterface::class);
        $data = $this->createMock(OAuth2Data::class);
        $command = new IssueAccessTokenCommand($data);

        $accessTokenData = new AccessTokenData(
            token: 'access_token',
            expiresIn: 3600
        );
        $refreshTokenData = new RefreshTokenData(
            token: 'refresh_token',
            expiresIn: 86400
        );
        $expectedResponse = new TokenResponseData(
            accessTokenData: $accessTokenData,
            refreshTokenData: $refreshTokenData
        );

        $tokenService->expects(self::once())
            ->method('handle')
            ->with($data)
            ->willReturn($expectedResponse);

        $handler = new IssueAccessTokenHandler($tokenService);
        $result = $handler($command);

        self::assertSame($expectedResponse, $result);
    }
}
