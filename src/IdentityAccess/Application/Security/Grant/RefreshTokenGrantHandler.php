<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\Contracts\RefreshTokenCredentialsInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use App\IdentityAccess\Application\Exception\InvalidRefreshTokenException;
use App\IdentityAccess\Application\Exception\RefreshToken\CreateRefreshTokenException;
use App\IdentityAccess\Application\Exception\TokenGenerateException;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant\RefreshTokenGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenServiceInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenTokenHashException;
use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\TokenHasherInterface;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class RefreshTokenGrantHandler implements GrantHandlerInterface
{
    public function __construct(
        #[AutowireLocator(
            services: 'identity_access.account_provider.refresh_token_grant',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
        private RefreshTokenReadRepositoryInterface $refreshTokenReadRepository,
        private ClockInterface $clock,
        private TokenHasherInterface $tokenHasher,
        private TokenGeneratorInterface $tokenGenerator,
        private RefreshTokenServiceInterface $refreshTokenService,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return GrantTypeEnum::RefreshToken->value;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws GrantHandlerException
     * @throws NotFoundExceptionInterface
     * @throws UnsupportedAccountProviderException
     */
    public function handle(RefreshTokenCredentialsInterface $data): TokenResponseData
    {
        try {
            $tokenHash = $this->tokenHasher->hash($data->getRefreshToken());

            $refreshToken = $this->refreshTokenReadRepository->findByToken(TokenHash::fromString($tokenHash));

            if (null === $refreshToken) {
                throw new InvalidRefreshTokenException('Invalid refresh token');
            }

            if ($refreshToken->getExpiresAt()->isExpired($this->clock)) {
                $this->refreshTokenService->revoke($refreshToken);

                throw new InvalidRefreshTokenException('Refresh token expired');
            }

            $providerId = $refreshToken->getAccountType()->value()->value;

            if (!$this->providers->has($providerId)) {
                throw new UnsupportedAccountProviderException(sprintf("Container does not have '%s' account provider for '%s' grant type handler", $providerId, self::getDefaultIndexName()));
            }

            $provider = $this->providers->get($providerId);

            if ($provider instanceof RefreshTokenGrantAccountProviderInterface) {
                $grantResult = $provider->handle($refreshToken->getAccountUlid()->value())
                    ?? throw new InvalidRefreshTokenException('Account not found');

                $accessTokenData = $this->tokenGenerator->generateAccessToken($grantResult);
                $refreshTokenData = $this->refreshTokenService->create(
                    accountUlid: $grantResult->subjectUlid,
                    accountType: $grantResult->subjectType
                );

                return new TokenResponseData(accessTokenData: $accessTokenData, refreshTokenData: $refreshTokenData);
            }

            throw new UnsupportedAccountProviderException(sprintf('Account provider %s is not an instance of %s', get_debug_type($provider), RefreshTokenGrantAccountProviderInterface::class));
        } catch (
            CreateRefreshTokenException
            |InvalidRefreshTokenTokenHashException
            |TokenGenerateException $e
        ) {
            throw new InvalidRefreshTokenException(message: 'Failed to process refresh token', previous: $e);
        }
    }
}
