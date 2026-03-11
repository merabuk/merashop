<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\Contracts\RefreshTokenCredentialsInterface;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\Exception\InvalidRefreshTokenException;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Grant\RefreshTokenGrantHandler;
use App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant\RefreshTokenGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenServiceInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\TokenHasherInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\RefreshTokenMother;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

final class RefreshTokenGrantHandlerTest extends TestCase
{
    private ContainerInterface&MockObject $container;
    private RefreshTokenReadRepositoryInterface&MockObject $readRepository;
    private TokenHasherInterface&MockObject $tokenHasher;
    private TokenGeneratorInterface&MockObject $tokenGenerator;
    private RefreshTokenServiceInterface&MockObject $refreshTokenService;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->readRepository = $this->createMock(RefreshTokenReadRepositoryInterface::class);
        $this->tokenHasher = $this->createMock(TokenHasherInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->refreshTokenService = $this->createMock(RefreshTokenServiceInterface::class);
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(GrantTypeEnum::RefreshToken->value, $this->createHandler()::getDefaultIndexName());
    }

    public function testItSuccessfullyIssuesTokens(): void
    {
        $credentials = $this->createRefreshTokenCredentialsMock();

        $clock = new MockClock();

        $this->tokenHasher->method('hash')->willReturn('hashed_token');
        $this->readRepository->method('findByToken')
            ->willReturn(RefreshTokenMother::createWithData(expiresAt: $clock->now()->modify('+1 hour')));

        $grantResult = new GrantResultData(
            subjectUlid: UserAccountMother::DEFAULT_ULID,
            subjectType: IdentityTypeEnum::User,
            roles: [RoleEnum::User->value]
        );

        $provider = $this->createMock(RefreshTokenGrantAccountProviderInterface::class);
        $provider->method('handle')->willReturn($grantResult);

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($provider);

        $accessTokenData = new AccessTokenData(token: 'access_token', expiresIn: 3600);
        $refreshTokenData = new RefreshTokenData(token: 'refresh_token', expiresIn: 86400);

        $this->tokenGenerator->method('generateAccessToken')->with($grantResult)->willReturn($accessTokenData);
        $this->refreshTokenService->method('create')
            ->with($grantResult->subjectUlid, $grantResult->subjectType)
            ->willReturn($refreshTokenData);

        $result = $this->createHandler($clock)->handle($credentials);

        self::assertSame($accessTokenData, $result->accessTokenData);
        self::assertSame($refreshTokenData, $result->refreshTokenData);
    }

    public function testThrowsExceptionWhenRefreshTokenNotFound(): void
    {
        $credentials = $this->createRefreshTokenCredentialsMock();

        $this->tokenHasher->method('hash')->willReturn('hashed_token');
        $this->readRepository->method('findByToken')
            ->willReturn(null);

        $this->expectException(InvalidRefreshTokenException::class);
        $this->createHandler()->handle($credentials);
    }

    public function testThrowsExceptionWhenRefreshTokenHasExpired(): void
    {
        $credentials = $this->createRefreshTokenCredentialsMock();

        $clock = new MockClock();

        $this->tokenHasher->method('hash')->willReturn('hashed_token');
        $this->readRepository->method('findByToken')
            ->willReturn(RefreshTokenMother::createWithData(expiresAt: $clock->now()->modify('-1 hour')));

        $this->expectException(InvalidRefreshTokenException::class);
        $this->createHandler($clock)->handle($credentials);
    }

    public function testThrowsExceptionWhenProviderMissingInContainer(): void
    {
        $credentials = $this->createRefreshTokenCredentialsMock();

        $clock = new MockClock();

        $this->tokenHasher->method('hash')->willReturn('hashed_token');
        $this->readRepository->method('findByToken')
            ->willReturn(RefreshTokenMother::createWithData(expiresAt: $clock->now()->modify('+1 hour')));

        $this->container->method('has')->with('user')->willReturn(false);

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->createHandler($clock)->handle($credentials);
    }

    public function testThrowsExceptionWhenProviderHasWrongType(): void
    {
        $credentials = $this->createRefreshTokenCredentialsMock();

        $clock = new MockClock();

        $this->tokenHasher->method('hash')->willReturn('hashed_token');
        $this->readRepository->method('findByToken')
            ->willReturn(RefreshTokenMother::createWithData(expiresAt: $clock->now()->modify('+1 hour')));

        $this->container->method('has')->with('user')->willReturn(true);
        $this->container->method('get')->with('user')->willReturn(new stdClass());

        $this->expectException(UnsupportedAccountProviderException::class);
        $this->createHandler($clock)->handle($credentials);
    }

    private function createRefreshTokenCredentialsMock(
        string $refreshToken = 'token',
    ): RefreshTokenCredentialsInterface {
        $credentials = $this->createMock(RefreshTokenCredentialsInterface::class);

        $credentials->method('getRefreshToken')->willReturn($refreshToken);

        return $credentials;
    }

    private function createHandler(
        ?ClockInterface $clock = null,
    ): RefreshTokenGrantHandler {
        $clock ??= new MockClock();

        return new RefreshTokenGrantHandler(
            providers: $this->container,
            refreshTokenReadRepository: $this->readRepository,
            clock: $clock,
            tokenHasher: $this->tokenHasher,
            tokenGenerator: $this->tokenGenerator,
            refreshTokenService: $this->refreshTokenService
        );
    }
}
