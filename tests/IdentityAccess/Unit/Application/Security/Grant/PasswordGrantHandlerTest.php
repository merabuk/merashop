<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Grant\PasswordGrantHandler;
use App\IdentityAccess\Application\Security\Provider\PasswordGrant\PasswordGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface as JwtGenerator;
use App\IdentityAccess\Application\Service\RefreshTokenServiceInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

final class PasswordGrantHandlerTest extends TestCase
{
    private ContainerInterface $providers;
    private JwtGenerator $tokenGenerator;
    private RefreshTokenServiceInterface $refreshTokenService;
    private PasswordGrantHandler $handler;

    protected function setUp(): void
    {
        $this->providers = $this->createMock(ContainerInterface::class);
        $this->tokenGenerator = $this->createMock(JwtGenerator::class);
        $this->refreshTokenService = $this->createMock(RefreshTokenServiceInterface::class);

        $this->handler = $this->creatHandler();
    }

    public function testItSuccessfullyIssuesTokens(): void
    {
        $accountType = IdentityTypeEnum::User;

        $credentials = $this->createUserCredentialsMock(accountType: $accountType);

        $grantResult = new GrantResultData(
            subjectUlid: UserAccountMother::DEFAULT_ULID,
            subjectType: $accountType,
            roles: [RoleEnum::User->value]
        );

        $provider = $this->createMock(PasswordGrantAccountProviderInterface::class);
        $provider->method('handle')->willReturn($grantResult);

        $this->providers->method('has')->with('user')->willReturn(true);
        $this->providers->method('get')->with('user')->willReturn($provider);

        $accessTokenData = new AccessTokenData(token: 'access_token', expiresIn: 3600);
        $refreshTokenData = new RefreshTokenData(token: 'refresh_token', expiresIn: 86400);

        $this->tokenGenerator->method('generateAccessToken')->with($grantResult)->willReturn($accessTokenData);
        $this->refreshTokenService->method('create')
            ->with($grantResult->subjectUlid, $grantResult->subjectType)
            ->willReturn($refreshTokenData);

        $result = $this->handler->handle($credentials);

        self::assertSame($accessTokenData, $result->accessTokenData);
        self::assertSame($refreshTokenData, $result->refreshTokenData);
    }

    public function testThrowsExceptionWhenProviderMissingInContainer(): void
    {
        $credentials = $this->createUserCredentialsMock();

        $this->providers->method('has')->with('user')->willReturn(false);

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->handler->handle($credentials);
    }

    public function testThrowsExceptionWhenProviderHasWrongType(): void
    {
        $credentials = $this->createUserCredentialsMock();

        $this->providers->method('has')->with('user')->willReturn(true);
        $this->providers->method('get')->with('user')->willReturn(new stdClass());

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->handler->handle($credentials);
    }

    private function createUserCredentialsMock(
        IdentityTypeEnum $accountType = IdentityTypeEnum::User,
        string $username = UserAccountMother::DEFAULT_EMAIL,
        string $password = 'password'
    ): UserCredentialsInterface {
        $credentials = $this->createMock(UserCredentialsInterface::class);

        $credentials->method('getAccountType')->willReturn($accountType);
        $credentials->method('getUsername')->willReturn($username);
        $credentials->method('getPassword')->willReturn($password);

        return $credentials;
    }

    private function creatHandler(): PasswordGrantHandler
    {
        return new PasswordGrantHandler(
            providers: $this->providers,
            tokenGenerator: $this->tokenGenerator,
            refreshTokenService: $this->refreshTokenService
        );
    }
}
