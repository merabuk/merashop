<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\ClientCredentialsInterface;
use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Application\Security\Grant\ClientCredentialsGrantHandler;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use PHPUnit\Framework\TestCase;

class ClientCredentialsGrantHandlerTest extends TestCase
{
    private ModuleAccountReadRepositoryInterface $readRepository;
    private PasswordHasherInterface $passwordHasher;
    private TokenGeneratorInterface $tokenGenerator;
    private ClientCredentialsGrantHandler $handler;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(ModuleAccountReadRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $this->handler = $this->createHandler();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(GrantTypeEnum::ClientCredentials->value, $this->handler::getDefaultIndexName());
    }

    public function testItSuccessfullyIssuesTokens(): void
    {
        $credentials = $this->createClientCredentialsMock();

        $this->readRepository->expects(self::once())
            ->method('findByClientId')
            ->willReturn(ModuleAccountMother::createWithData());

        $this->passwordHasher->method('verify')->willReturn(true);

        $accessTokenData = new AccessTokenData(token: 'access_token', expiresIn: 3600);
        $this->tokenGenerator->method('generateAccessToken')->willReturn($accessTokenData);

        $result = $this->handler->handle($credentials);

        self::assertSame($accessTokenData, $result->accessTokenData);
        self::assertNull($result->refreshTokenData);
    }

    public function testThrowsExceptionWhenModuleAccountNotFound(): void
    {
        $credentials = $this->createClientCredentialsMock();

        $this->readRepository->expects(self::once())
            ->method('findByClientId')
            ->willReturn(null);

        $this->expectException(InvalidClientException::class);
        $this->handler->handle($credentials);
    }

    public function testThrowsExceptionWhenGivenPasswordIncorrect(): void
    {
        $credentials = $this->createClientCredentialsMock();

        $this->readRepository->expects(self::once())
            ->method('findByClientId')
            ->willReturn(ModuleAccountMother::createWithData());

        $this->passwordHasher->method('verify')->willReturn(false);

        $this->expectException(InvalidClientException::class);
        $this->handler->handle($credentials);
    }

    private function createClientCredentialsMock(
        string $clientId = ModuleAccountMother::DEFAULT_CLIENT_ID,
        string $clientSecret = 'client_secret',
    ): ClientCredentialsInterface {
        $credentials = $this->createMock(ClientCredentialsInterface::class);

        $credentials->method('getClientId')->willReturn($clientId);
        $credentials->method('getClientSecret')->willReturn($clientSecret);

        return $credentials;
    }

    private function createHandler(): ClientCredentialsGrantHandler
    {
        return new ClientCredentialsGrantHandler(
            moduleAccountReadRepository: $this->readRepository,
            passwordHasher: $this->passwordHasher,
            tokenGenerator: $this->tokenGenerator,
        );
    }
}
