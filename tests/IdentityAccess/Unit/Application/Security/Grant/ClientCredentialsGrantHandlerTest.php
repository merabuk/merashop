<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\Contracts\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Grant\ClientCredentialsGrantHandler;
use App\IdentityAccess\Application\Security\Provider\ClientCredentialsGrant\ClientCredentialsGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use stdClass;

final class ClientCredentialsGrantHandlerTest extends BaseUnitTest
{
    private ContainerInterface&MockObject $providers;
    private TokenGeneratorInterface&MockObject $tokenGenerator;
    private ClientCredentialsGrantHandler $handler;

    protected function setUp(): void
    {
        $this->providers = $this->createMock(ContainerInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $this->handler = $this->createHandler();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(GrantTypeEnum::ClientCredentials->value, $this->handler::getDefaultIndexName());
    }

    public function testItSuccessfullyIssuesTokens(): void
    {
        $accountType = IdentityTypeEnum::Module;

        $credentials = $this->createClientCredentialsMock();

        $grantResult = new GrantResultData(
            subjectUlid: ModuleAccountMother::DEFAULT_ULID,
            subjectType: $accountType,
            roles: [],
            scopes: [RoleEnum::Module->value]
        );

        $provider = $this->createMock(ClientCredentialsGrantAccountProviderInterface::class);
        $provider->method('handle')->willReturn($grantResult);

        $this->providers->method('has')->with($accountType->value)->willReturn(true);
        $this->providers->method('get')->with($accountType->value)->willReturn($provider);

        $accessTokenData = new AccessTokenData(token: 'access_token', expiresIn: 3600);
        $this->tokenGenerator->method('generateAccessToken')->willReturn($accessTokenData);

        $result = $this->handler->handle($credentials);

        self::assertSame($accessTokenData, $result->accessTokenData);
        self::assertNull($result->refreshTokenData);
    }

    public function testThrowsExceptionWhenProviderMissingInContainer(): void
    {
        $credentials = $this->createClientCredentialsMock();

        $this->providers->method('has')->with(IdentityTypeEnum::Module->value)->willReturn(false);

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->handler->handle($credentials);
    }

    public function testThrowsExceptionWhenProviderHasWrongType(): void
    {
        $credentials = $this->createClientCredentialsMock();

        $this->providers->method('has')->with(IdentityTypeEnum::Module->value)->willReturn(true);
        $this->providers->method('get')->with(IdentityTypeEnum::Module->value)->willReturn(new stdClass());

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->handler->handle($credentials);
    }

    private function createClientCredentialsMock(
        IdentityTypeEnum $accountType = IdentityTypeEnum::Module,
        string $clientId = ModuleAccountMother::DEFAULT_CLIENT_ID,
        string $clientSecret = 'client_secret',
    ): ClientCredentialsInterface {
        $credentials = $this->createMock(ClientCredentialsInterface::class);

        $credentials->method('getAccountType')->willReturn($accountType);
        $credentials->method('getClientId')->willReturn($clientId);
        $credentials->method('getClientSecret')->willReturn($clientSecret);

        return $credentials;
    }

    private function createHandler(): ClientCredentialsGrantHandler
    {
        return new ClientCredentialsGrantHandler(
            providers: $this->providers,
            tokenGenerator: $this->tokenGenerator,
        );
    }
}
